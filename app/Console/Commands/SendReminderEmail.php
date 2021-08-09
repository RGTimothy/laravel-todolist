<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Util;
use App\TodoItem;
use App\Jobs\SendEmailJob;
use App\Mail\DueDateReminder;

class SendReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SendReminderEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all to-do items and send the reminder emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $currentUnixTimestamp = Util::getCurrentUnixTimestamp();

            $query = TodoItem::_getList();
            $query->whereNull('todo_items.deleted_at')
                  ->where('todo_items.status', TodoItem::STATUS_INCOMPLETE)
                  ->whereNotNull('todo_items.due_date')
                  ->where('todo_items.is_email_sent', 0);

            $pendingList = $query->get();

            $sentIds = [];

            // check which item needs to receive an email
            foreach ($pendingList as $item) {
                $reminderTime = $item->due_date_unix - $item->reminder_unix_value;

                if ($currentUnixTimestamp > $reminderTime) {
                    // send reminder email
                    $emailData = [
                        'title' => $item->title,
                        'body' => $item->body,
                        'due_date' => $item->due_date,
                        'status' => $item->status,
                        'created_at' => $item->created_at
                    ];
                    $job = (new SendEmailJob(new DueDateReminder($emailData), $item->user_email));
                    dispatch($job);

                    array_push($sentIds, $item->id);
                }
            }

            $totalEmailSent = count($sentIds);

            // update database to mark sent items
            TodoItem::whereIn('todo_items.id', $sentIds)->update(['is_email_sent' => 1]);
            
            $this->info('Sent ' . $totalEmailSent . ' reminder emails to users');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
