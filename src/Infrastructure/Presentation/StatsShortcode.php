<?php
declare(strict_types=1);
namespace MyFinance\Infrastructure\Presentation;

class StatsShortcode {
    
    public function registerHooks(): void {
        add_shortcode('finance_stats', [$this, 'renderShortcode']);
        
        add_action('wp_ajax_get_finance_chart_data', [$this, 'getChartData']);
        add_action('wp_ajax_nopriv_get_finance_chart_data', [$this, 'getChartData']);
    }

    public function getChartData(): void {
        $args = [
            'post_type'      => 'fin_transaction',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'meta_value',
            'meta_key'       => 'fin_date',
            'order'          => 'ASC'
        ];
        
        $query = new \WP_Query($args);
        
        $bar_months = [];
        $bar_income_data = [];
        $bar_expense_data = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $amount = (float) get_post_meta(get_the_ID(), 'fin_amount', true);
                $type   = get_post_meta(get_the_ID(), 'fin_type', true);
                $date   = get_post_meta(get_the_ID(), 'fin_date', true);

                $month_year = date('m/Y', strtotime($date));

                if (!in_array($month_year, $bar_months)) {
                    $bar_months[] = $month_year;
                    $bar_income_data[$month_year] = 0;
                    $bar_expense_data[$month_year] = 0;
                }

                if ($type === 'expense') {
                    $bar_expense_data[$month_year] += $amount;
                } else {
                    $bar_income_data[$month_year] += $amount;
                }
            }
            wp_reset_postdata();
        }

        wp_send_json_success([
            'labels'   => $bar_months,
            'incomes'  => array_values($bar_income_data),
            'expenses' => array_values($bar_expense_data)
        ]);
    }

    public function renderShortcode($atts): string {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0', true);

        ob_start();
        ?>
        <div class="finance-stats-wrap" style="max-width: 800px; margin: 40px auto; font-family: sans-serif;">
            <h2>Financial Stats</h2>

            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px;">
                <canvas id="cashFlowBarChart"></canvas>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', () => {
                
                async function fetchChartData() {
                    const formData = new FormData();
                    formData.append('action', 'get_finance_chart_data');

                    try {
                        const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        });
                        const res = await response.json();

                        if (res.success) {
                            drawChart(res.data);
                        }
                    } catch (error) {
                        console.error("Σφάλμα:", error);
                    }
                }

                function drawChart(data) {
                    const ctxBar = document.getElementById('cashFlowBarChart').getContext('2d');
                    new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Έσοδα',
                                    data: data.incomes,
                                    backgroundColor: '#4CAF50'
                                },
                                {
                                    label: 'Έξοδα',
                                    data: data.expenses,
                                    backgroundColor: '#F44336'
                                }
                            ]
                        }
                    });
                }

                fetchChartData();
            });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}