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
        $fin_type  = isset($_POST['fin_type']) ? sanitize_text_field($_POST['fin_type']) : '';

        $args = [
            'post_type'      => 'fin_transaction',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'fin_date',
            'orderby'        => 'meta_value',
            'order'          => 'DESC'
        ];

        $meta_query = ['relation' => 'AND'];

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

            if (!empty($fin_type) && in_array($fin_type, ['income', 'expense'])) {
            $meta_query[] = [
                'key'     => 'fin_type',
                'value'   => $fin_type,
                'compare' => '='
            ];
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
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
                    $type_label = '<span style="color:green; font-weight:bold;">' . esc_html__('Income', 'my-finance') . '</span>';
                } else {
                    $total_balance -= $amount;
                    $type_label = '<span style="color:red; font-weight:bold;">' . esc_html__('Expense', 'my-finance') . '</span>';
                }

                $html .= '<tr style="border-bottom: 1px solid #eee;">';
                $html .= '<td style="padding: 10px 0;">' . get_the_title() . '</td>';
                $html .= '<td>' . esc_html($date) . '</td>';
                $html .= '<td>' . $type_label . '</td>';
                $html .= '<td><strong>' . number_format($amount, 2, '.', ',') . ' €</strong></td>';
                $html .= '</tr>';
            }
            wp_reset_postdata();
        } else {
           $html = '<tr><td colspan="4" style="padding:20px; text-align:center;">' . esc_html__('No results found for this range.', 'my-finance') . '</td></tr>';
        }

        
        wp_send_json_success([
            'table_html'    => $html,
            'total_balance' => number_format($total_balance, 2, '.', ',') . ' €',
            'is_positive'   => ($total_balance >= 0)
        ]);
    }
    }

   public function render($atts) {
        ob_start(); 
        ?>
        <div class="wrap" style="max-width: 800px; margin: 40px auto; padding: 20px; font-family: sans-serif;">
            <h2><?php esc_html_e('Financial Dashboard', 'my-finance'); ?></h2>

            <div style="margin-bottom: 20px; padding: 15px; background: #f1f1f1; border-radius: 5px; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div>
                    <label for="fin_date_from"><strong><?php esc_html_e('From:', 'my-finance'); ?></strong></label><br>
                    <input type="date" id="fin_date_from" name="fin_date_from">
                </div>
                <div>
                    <label for="fin_date_to"><strong><?php esc_html_e('To:', 'my-finance'); ?></strong></label><br>
                    <input type="date" id="fin_date_to" name="fin_date_to">
                </div>
                
                <div>
                    <label for="fin_type_filter"><strong><?php esc_html_e('Type:', 'my-finance'); ?></strong></label><br>
                    <select id="fin_type_filter" name="fin_type_filter" style="padding: 4px;">
                        <option value=""><?php esc_html_e('All', 'my-finance'); ?></option>
                        <option value="income"><?php esc_html_e('Income', 'my-finance'); ?></option>
                        <option value="expense"><?php esc_html_e('Expense     ', 'my-finance'); ?></option>
                    </select>
                </div>

                <div>
                    <button type="button" id="fin_filter_btn" style="padding: 6px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">
                        <?php esc_html_e('Filter', 'my-finance'); ?>
                    </button>
                    <button type="button" id="fin_clear_btn" style="padding: 6px 15px; background: #dc3232; color: white; border: none; border-radius: 3px; cursor: pointer; margin-left: 5px;">
                        <?php esc_html_e('Clear All', 'my-finance'); ?>
                    </button>
                </div>
            </div>

            <?php
            $args = [
                'post_type'      => 'fin_transaction',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_key'       => 'fin_date',
                'orderby'        => 'meta_value',
                'order'          => 'DESC'
            ];
            $finance_query = new \WP_Query($args);

            if ($finance_query->have_posts()) : ?>
                <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ccc;">
                            <th><?php esc_html_e('Title', 'my-finance'); ?></th>
                            <th><?php esc_html_e('Date', 'my-finance'); ?></th>
                            <th><?php esc_html_e('Type', 'my-finance'); ?></th>
                            <th><?php esc_html_e('Amount (€)', 'my-finance'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="finance-table-body">
                    <?php
                    
                    $total_balance = 0; 

                    while ($finance_query->have_posts()) : $finance_query->the_post();
                        $amount = (float) get_post_meta(get_the_ID(), 'fin_amount', true);
                        $date   = get_post_meta(get_the_ID(), 'fin_date', true);
                        $type   = get_post_meta(get_the_ID(), 'fin_type', true);
                        
                        if ($type === 'income') {
                            $total_balance += $amount;
                            $type_label = '<span style="color:green; font-weight:bold;">' . esc_html__('Income', 'my-finance') . '</span>';
                        } else {
                            $total_balance -= $amount;
                            $type_label = '<span style="color:red; font-weight:bold;">' . esc_html__('Expense', 'my-finance') . '</span>';
                        }
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px 0;"><?php the_title(); ?></td>
                            <td><?php echo esc_html($date); ?></td>
                            <td><?php echo $type_label; ?></td>
                            <td><strong><?php echo number_format($amount, 2, '.', ','); ?> €</strong></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
            
                    <tfoot>
                        <tr style="background-color: #f9f9f9; border-top: 2px solid #333;">
                            <td colspan="3" style="text-align: right; padding: 15px 10px;"><strong><?php esc_html_e('Final Amount:', 'my-finance'); ?></strong></td>
                            <td style="padding: 15px 10px; font-size: 1.1em;">
                                <strong id="finance-total-balance" style="color: <?php echo ($total_balance >= 0) ? 'green' : 'red'; ?>;">
                                    <?php echo number_format($total_balance, 2, '.', ','); ?> €
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p id="finance-no-results"><?php esc_html_e('No Transactions found.', 'my-finance'); ?></p>
            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtn = document.getElementById('fin_filter_btn');
            const clearBtn  = document.getElementById('fin_clear_btn');
            
            function fetchFilteredData() {
                const dateFrom = document.getElementById('fin_date_from').value;
                const dateTo   = document.getElementById('fin_date_to').value;
                const finType  = document.getElementById('fin_type_filter').value;
                
                const originalText = filterBtn.innerText;
                filterBtn.innerText = '<?php esc_html_e('Loading...', 'my-finance'); ?>';
                filterBtn.disabled = true;

                const formData = new FormData();
                formData.append('action', 'filter_finance_dashboard');
                formData.append('date_from', dateFrom);
                formData.append('date_to', dateTo);
                formData.append('fin_type', finType);

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

                    filterBtn.innerText = originalText;
                    filterBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    filterBtn.innerText = originalText;
                    filterBtn.disabled = false;
                });
            }

            if (filterBtn) {
                filterBtn.addEventListener('click', fetchFilteredData);
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    document.getElementById('fin_date_from').value = '';
                    document.getElementById('fin_date_to').value = '';
                    document.getElementById('fin_type_filter').value = '';
                    
                    fetchFilteredData();
                });
            }
        });
        </script>

        <?php
        return ob_get_clean(); 
    }
}