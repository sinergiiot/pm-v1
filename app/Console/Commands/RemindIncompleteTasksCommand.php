<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\IncompleteTasksReminderNotification;
use Illuminate\Console\Command;

class RemindIncompleteTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:incomplete-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminder to users who have tasks not yet done (status != Done)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $users = User::query()
            ->whereHas('tasks', function ($query): void {
                $query->whereHas('taskStatus', function ($q): void {
                    $q->where('name', '!=', 'Done');
                });
            })
            ->with([
                'tasks' => function ($query): void {
                    $query->whereHas('taskStatus', function ($q): void {
                        $q->where('name', '!=', 'Done');
                    })
                        ->with(['taskStatus', 'project']);
                },
            ])
            ->get();

        $sent = 0;
        foreach ($users as $user) {
            $incompleteTasks = $user->tasks;

            if ($incompleteTasks->isEmpty()) {
                continue;
            }

            $user->notifyNow(new IncompleteTasksReminderNotification($user, $incompleteTasks));
            $sent++;
            $this->info("Reminder sent to: {$user->email} ({$incompleteTasks->count()} task(s))");
        }

        $this->info("Done. {$sent} reminder(s) sent.");

        return self::SUCCESS;
    }
}
