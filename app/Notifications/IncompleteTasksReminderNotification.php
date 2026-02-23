<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompleteTasksReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  \Illuminate\Support\Collection<int, Task>  $incompleteTasks
     */
    public function __construct(
        public User $user,
        public \Illuminate\Support\Collection $incompleteTasks
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->incompleteTasks->count();
        $subject = $count === 1
            ? 'Reminder: Anda punya 1 task yang belum selesai'
            : "Reminder: Anda punya {$count} task yang belum selesai";

        $tasks = $this->incompleteTasks->take(20);
        $firstProject = $this->incompleteTasks->first()?->project;
        $taskListUrl = $firstProject
            ? route('filament.admin.resources.projects.task-list', ['record' => $firstProject->id])
            : null;
        $extraCount = $this->incompleteTasks->count() - 20;

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.incomplete-tasks-reminder', [
                'user' => $this->user,
                'tasks' => $tasks,
                'taskListUrl' => $taskListUrl,
                'extraCount' => $extraCount > 0 ? $extraCount : 0,
            ]);
    }
}
