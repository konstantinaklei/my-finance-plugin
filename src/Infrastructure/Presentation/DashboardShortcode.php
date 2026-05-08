<?php
namespace MyFinance\Infrastructure\Presentation;

if (!defined('ABSPATH')) exit;

class DashboardShortcode {
    
    public function registerHooks() {
        add_shortcode('finance_dashboard', [$this, 'render']);
        //AJAX for logged-in users
        add_action('wp_ajax_filter_finance_dashboard', [$this, 'handleAjaxFilter']);
        //AJAX for visitors
        add_action('wp_ajax_nopriv_filter_finance_dashboard', [$this, 'handleAjaxFilter']);
    }
    
    public function handleAjaxFilter() {
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to   = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

        $args = [
            'post_type'      => 'fin_transaction',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'fin_date',
            'orderby'        => 'meta_value',
            'order'          => 'DESC'
        ];

        if (!empty($date_from) || !empty($date_to)) {
            $args['meta_query'] = ['relation' => 'AND'];
            
            if (!empty($date_from)) {
                $args['meta_query'][] = [
                    'key'     => 'fin_date',
                    'value'   => $date_from,
                    'compare' => '>=',
                    'type'    => 'DATE'
                ];
            }
            if (!empty($date_to)) {
                $args['meta_query'][] = [
                    'key'     => 'fin_date',
                    'value'   => $date_to,
                    'compare' => '<=',
                    'type'    => 'DATE'
                ];
            }
        }

        $query = new \WP_Query($args);
        $html = '';
        $total_balance = 0;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $amount = (float) get_post_meta(get_the_ID(), 'fin_amount', true);
                $type   = get_post_meta(get_the_ID(), 'fin_type', true);
                $date   = get_post_meta(get_the_ID(), 'fin_date', true);

                if ($type === 'income') {
                    $total_balance += $amount;
                    $type_label = '<span style="color:green; font-weight:bold;">Έσοδο</span>';
                } else {
                    $total_balance -= $amount;
                    $type_label = '<span style="color:red; font-weight:bold;">Έξοδο</span>';
                }

                $html .= '<tr style="border-bottom: 1px solid #eee;">';
                $html .= '<td style="padding: 10px 0;">' . get_the_title() . '</td>';
                $html .= '<td>' . esc_html($date) . '</td>';
                $html .= '<td>' . $type_label . '</td>';
                $html .= '<td><strong>' . number_format($amount, 2, ',', '.') . ' €</strong></td>';
                $html .= '</tr>';
            }
            wp_reset_postdata();
        } else {
            $html = '<tr><td colspan="4" style="padding:20px; text-align:center;">No results found for this range.</td></tr>';
        }

        
        wp_send_json_success([
            'table_html'    => $html,
            'total_balance' => number_format($total_balance, 2, ',', '.') . ' €',
            'is_positive'   => ($total_balance >= 0)
        ]);
    }

    public function render($atts) {
        ob_start(); 
        ?>
        <div class="wrap" style="max-width: 800px; margin: 40px auto; padding: 20px; font-family: sans-serif;">
            <h2>Financial Dashboard</h2>

            <div style="margin-bottom: 20px; padding: 15px; background: #f1f1f1; border-radius: 5px; display: flex; gap: 15px; align-items: center;">
                <div>
                    <label for="fin_date_from"><strong>From:</strong></label><br>
                    <input type="date" id="fin_date_from" name="fin_date_from">
                </div>
                <div>
                    <label for="fin_date_to"><strong>To:</strong></label><br>
                    <input type="date" id="fin_date_to" name="fin_date_to">
                </div>
                <div>
                    <br>
                    <button type="button" id="fin_filter_btn" style="padding: 6px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Filter</button>
                </div>
            </div>

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
                    <tbody id="finance-table-body">
                    <?php
                    
                    $total_balance = 0; 

                    while ($finance_query->have_posts()) : $finance_query->the_post();
                        $amount = (float) get_post_meta(get_the_ID(), 'fin_amount', true);
                        $date   = get_post_meta(get_the_ID(), 'fin_date', true);
                        $type   = get_post_meta(get_the_ID(), 'fin_type', true);
                        
                        //final amount
                        if ($type === 'income') {
                            $total_balance += $amount;
                            $type_label = '<span style="color:green; font-weight:bold;">Έσοδο</span>';
                        } else {
                            $total_balance -= $amount;
                            $type_label = '<span style="color:red; font-weight:bold;">Έξοδο</span>';
                        }
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px 0;"><?php the_title(); ?></td>
                            <td><?php echo esc_html($date); ?></td>
                            <td><?php echo $type_label; ?></td>
                            <td><strong><?php echo number_format($amount, 2, ',', '.'); ?> €</strong></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
            
                    <tfoot>
                        <tr style="background-color: #f9f9f9; border-top: 2px solid #333;">
                            <td colspan="3" style="text-align: right; padding: 15px 10px;"><strong>Final Amount:</strong></td>
                            <td style="padding: 15px 10px; font-size: 1.1em;">
                                <strong id="finance-total-balance" style="color: <?php echo ($total_balance >= 0) ? 'green' : 'red'; ?>;">
                                    <?php echo number_format($total_balance, 2, ',', '.'); ?> €
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p id="finance-no-results">No Transactions found.</p>
            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('fin_filter_btn');
            
            if (btn) {
                btn.addEventListener('click', function() {
                    const dateFrom = document.getElementById('fin_date_from').value;
                    const dateTo = document.getElementById('fin_date_to').value;
                    
                    // changing the button's text in loading...
                    const originalText = btn.innerText;
                    btn.innerText = 'Loading...';
                    btn.disabled = true;

                    const formData = new FormData();
                    formData.append('action', 'filter_finance_dashboard');
                    formData.append('date_from', dateFrom);
                    formData.append('date_to', dateTo);

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            document.getElementById('finance-table-body').innerHTML = res.data.table_html;
                            const balanceEl = document.getElementById('finance-total-balance');
                            balanceEl.innerText = res.data.total_balance;
                            balanceEl.style.color = res.data.is_positive ? 'green' : 'red';
                        }

                        btn.innerText = originalText;
                        btn.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.innerText = originalText;
                        btn.disabled = false;
                    });
                });
            }
        });
        </script>

        <?php
        return ob_get_clean(); 
    }
}