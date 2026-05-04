<?php
namespace MyFinance\Infrastructure\Presentation;

if (!defined('ABSPATH')) exit;

class DashboardShortcode {
    
    // Register shortcode in WordPress
    public function registerHooks() {
        add_shortcode('finance_dashboard', [$this, 'render']);
    }

    public function render($atts) {
        ob_start(); 
        ?>
        <div class="wrap" style="max-width: 800px; margin: 40px auto; padding: 20px; font-family: sans-serif;">
            <h2>Financial Dashboard</h2>

            <?php
            $args = [
                'post_type'      => 'fin_transaction',
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ];
            $finance_query = new \WP_Query($args);

            if ($finance_query->have_posts()) : ?>
                <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ccc;">
                            <th>Title</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($finance_query->have_posts()) : $finance_query->the_post();
                        $amount = get_post_meta(get_the_ID(), 'fin_amount', true);
                        $date   = get_post_meta(get_the_ID(), 'fin_date', true);
                        $type   = get_post_meta(get_the_ID(), 'fin_type', true);
                        
                        $type_label = ($type === 'income') ? '<span style="color:green; font-weight:bold;">Έσοδο</span>' : '<span style="color:red; font-weight:bold;">Έξοδο</span>';
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px 0;"><?php the_title(); ?></td>
                            <td><?php echo esc_html($date); ?></td>
                            <td><?php echo $type_label; ?></td>
                            <td><strong><?php echo esc_html($amount); ?> €</strong></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p>No Transactions found.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean(); 
    }
}