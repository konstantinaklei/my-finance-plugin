<?php
declare(strict_types=1);
namespace MyFinance\Infrastructure;

class FinanceCronManager {
    private const CRON_HOOK = 'finance_plugin_daily_task';

    public function init(): void {
        add_action(self::CRON_HOOK, [$this, 'handleDailyTask']);

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::CRON_HOOK);
        }
    }

    public function handleDailyTask(): void {
        error_log('Finance Plugin Cron Job ran successfully!');
        
    }

    public static function deactivate(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }
}