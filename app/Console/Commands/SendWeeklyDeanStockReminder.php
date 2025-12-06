<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Notifications\WeeklyStockUpdateReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendWeeklyDeanStockReminder extends Command
{
    protected $signature = 'notify:deans-stock-update';
    protected $description = 'Send weekly stock update reminders to department deans based on chosen weekday';

    public function handle(): int
    {
        $todayIsoDay = Carbon::now()->isoWeekday(); // 1..7 (Mon..Sun)

        Department::query()
            ->whereNotNull('stock_update_reminder_day')
            ->where('stock_update_reminder_day', $todayIsoDay)
            ->each(function (Department $department) {
                $dean = $department->dean;
                if (!$dean) {
                    return; // skip if no dean
                }

                try {
                    $dean->notify(new WeeklyStockUpdateReminderNotification($department));
                } catch (\Throwable $e) {
                    Log::warning('Failed to notify dean for department', [
                        'department_id' => $department->id,
                        'dean_id' => $dean->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        $this->info('Dean stock update reminders processed for ISO weekday: ' . $todayIsoDay);
        return self::SUCCESS;
    }
}