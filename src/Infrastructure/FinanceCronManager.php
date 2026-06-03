<?php
declare(strict_types=1);
namespace MyFinance\Infrastructure;

if (!defined('ABSPATH')) { exit; }

class FinanceCronManager {
    private const CRON_HOOK = 'finance_plugin_daily_task';

    public function init(): void {
        add_action(self::CRON_HOOK, [$this, 'handleDailyTask']);

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::CRON_HOOK);
        }
    }

    public function handleDailyTask(): void {
        $yesterday = wp_date('Y-m-d', strtotime('-1 days'));

        $args = [
        'post_type'  => 'fin_transaction',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key'   => 'fin_date',
                'value' => $yesterday,
            ],
            [
                'key'   => 'fin_type',
                'value' => 'expense',
            ]
        ]
    ];
    $query = new \WP_Query($args);
    $total_expenses = 0;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $total_expenses += (float) get_post_meta(get_the_ID(), 'fin_amount', true);
        }
        wp_reset_postdata();
    }

    if ($total_expenses > 0) {
        $admin_email = get_option('admin_email');
        wp_mail(
            $admin_email, 
            'Ημερήσια Αναφορά Εξόδων', 
            "Χθες ξοδέψατε συνολικά: {$total_expenses} €"
        );
    }
        //Debug
        //error_log('Finance Plugin Cron Job ran successfully!');
        
    }

    public static function deactivate(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }
}