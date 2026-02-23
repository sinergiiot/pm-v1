<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    protected const TOKEN_URL = 'https://zoom.us/oauth/token';

    protected const API_BASE = 'https://api.zoom.us/v2';

    /**
     * Get Server-to-Server OAuth access token using credentials from Settings.
     *
     * @return array{access_token: string}|null
     */
    public function getAccessToken(): ?array
    {
        $accountId = Setting::get('zoom_account_id');
        $clientId = Setting::get('zoom_client_id');
        $clientSecret = Setting::get('zoom_client_secret');

        if (blank($accountId) || blank($clientId) || blank($clientSecret)) {
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'account_credentials',
                'account_id' => $accountId,
            ]);

        if (! $response->successful()) {
            Log::warning('Zoom token request failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Create a scheduled Zoom meeting.
     *
     * @param  array{title: string, agenda?: string|null, start: string|\DateTimeInterface, end: string|\DateTimeInterface}  $payload
     * @return array{id: string, join_url: string, start_url: string}|null
     */
    public function createMeeting(array $payload): ?array
    {
        $token = $this->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return null;
        }

        $start = \Carbon\Carbon::parse($payload['start']);
        $end = \Carbon\Carbon::parse($payload['end']);
        $duration = (int) max(1, round($start->diffInMinutes($end, false)));

        // Zoom expects start_time in UTC (ISO 8601)
        $startUtc = $start->copy()->utc();

        $body = [
            'topic' => $payload['title'],
            'agenda' => $payload['agenda'] ?? '',
            'start_time' => $startUtc->format('Y-m-d\TH:i:s\Z'),
            'duration' => $duration,
            'timezone' => config('app.timezone', 'UTC'),
            'type' => 2, // scheduled meeting
            'settings' => [
                'host_video' => false,
                'participant_video' => false,
                'join_before_host' => true,
                'mute_upon_entry' => true,
            ],
        ];

        $response = Http::withToken($token['access_token'])
            ->post(self::API_BASE . '/users/me/meetings', $body);

        if (! $response->successful()) {
            Log::warning('Zoom create meeting failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        $data = $response->json();

        return [
            'id' => (string) ($data['id'] ?? ''),
            'join_url' => (string) ($data['join_url'] ?? ''),
            'start_url' => (string) ($data['start_url'] ?? ''),
        ];
    }

    /**
     * Delete/cancel a Zoom meeting by ID.
     */
    public function deleteMeeting(string $zoomMeetingId): bool
    {
        if (blank($zoomMeetingId)) {
            return false;
        }

        $token = $this->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return false;
        }

        $response = Http::withToken($token['access_token'])
            ->delete(self::API_BASE . '/meetings/' . $zoomMeetingId);

        if (! $response->successful()) {
            Log::warning('Zoom delete meeting failed', [
                'meeting_id' => $zoomMeetingId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Check if Zoom is configured (all required settings present).
     */
    public function isConfigured(): bool
    {
        return filled(Setting::get('zoom_account_id'))
            && filled(Setting::get('zoom_client_id'))
            && filled(Setting::get('zoom_client_secret'));
    }
}
