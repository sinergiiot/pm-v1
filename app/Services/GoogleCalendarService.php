<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected string $calendarId = 'primary';

    /**
     * Get a Google API client authenticated for the given user (using their stored OAuth tokens).
     * Refreshes the access token if expired and persists the new token.
     */
    public function getClientForUser(User $user): ?GoogleClient
    {
        $socialiteUser = $user->socialiteUsers()->where('provider', 'google')->first();

        if (! $socialiteUser || ! $socialiteUser->refresh_token) {
            return null;
        }

        $client = new GoogleClient;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setScopes([\Google\Service\Calendar::CALENDAR_EVENTS]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $expiresIn = 3600; // Google access tokens typically expire in 1 hour
        $token = [
            'access_token' => $socialiteUser->access_token,
            'refresh_token' => $socialiteUser->refresh_token,
            'expires_in' => $expiresIn,
            'created' => $socialiteUser->token_expires_at
                ? $socialiteUser->token_expires_at->getTimestamp() - $expiresIn
                : 0,
        ];

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            try {
                $newToken = $client->fetchAccessTokenWithRefreshToken($socialiteUser->refresh_token);

                if (isset($newToken['error'])) {
                    Log::warning('Google Calendar token refresh failed', [
                        'user_id' => $user->id,
                        'error' => $newToken['error'],
                    ]);
                    return null;
                }

                $socialiteUser->access_token = $newToken['access_token'] ?? $socialiteUser->access_token;
                $socialiteUser->refresh_token = $newToken['refresh_token'] ?? $socialiteUser->refresh_token;
                if (isset($newToken['expires_in'])) {
                    $socialiteUser->token_expires_at = now()->addSeconds($newToken['expires_in']);
                }
                $socialiteUser->save();

                $client->setAccessToken($newToken);
            } catch (\Throwable $e) {
                Log::warning('Google Calendar token refresh exception', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
                return null;
            }
        }

        return $client;
    }

    /**
     * Create a calendar event for a task and assignee. Returns the Google event ID or null.
     */
    public function createEventForTask(User $user, Task $task): ?string
    {
        if (! $task->due_date) {
            return null;
        }

        $client = $this->getClientForUser($user);
        if (! $client) {
            return null;
        }

        $calendar = new Calendar($client);
        $event = new Event([
            'summary' => $task->title,
            'description' => $task->description ?? '',
            'start' => [
                'date' => $task->due_date->format('Y-m-d'),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
            'end' => [
                'date' => $task->due_date->format('Y-m-d'),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
        ]);

        try {
            $created = $calendar->events->insert($this->calendarId, $event);
            return $created->getId();
        } catch (\Throwable $e) {
            Log::warning('Google Calendar create event failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update an existing calendar event for a task/assignee.
     */
    public function updateEventForTask(User $user, Task $task, string $eventId): bool
    {
        if (! $task->due_date) {
            return false;
        }

        $client = $this->getClientForUser($user);
        if (! $client) {
            return false;
        }

        $calendar = new Calendar($client);
        $event = new Event([
            'summary' => $task->title,
            'description' => $task->description ?? '',
            'start' => [
                'date' => $task->due_date->format('Y-m-d'),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
            'end' => [
                'date' => $task->due_date->format('Y-m-d'),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
        ]);

        try {
            $calendar->events->update($this->calendarId, $eventId, $event);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Google Calendar update event failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'event_id' => $eventId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a calendar event for an assignee.
     */
    public function deleteEventForUser(User $user, string $eventId): bool
    {
        $client = $this->getClientForUser($user);
        if (! $client) {
            return false;
        }

        try {
            $calendar = new Calendar($client);
            $calendar->events->delete($this->calendarId, $eventId);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Google Calendar delete event failed', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
