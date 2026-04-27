<?php
namespace MyFinance\Infrastructure;

if (!defined('ABSPATH')) exit;

class WordPressSetup {
    
    // Εδώ "δένουμε" τις συναρτήσεις μας με τα Hooks του WordPress
    public function registerHooks() {
        add_action('init', [$this, 'registerCoreStructures']);
        add_action('init', [$this, 'registerMetaFields']);
        add_action('add_meta_boxes', [$this, 'addCustomMetaBox']);
    }

    public function registerCoreStructures() {
        register_taxonomy('fin_category', 'fin_transaction', [
            'label' => 'Categories',
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);

        register_post_type('fin_transaction', [
            'labels' => [
                'name' => 'Transactions', 
                'singular_name' => 'Transaction',
                'add_new' => 'Add Transaction',
                'add_new_item' => 'Add New Transaction',
            ],
            'public' => true,
            'publicly_queryable' => false,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-chart-line',
            'supports' => ['editor'], 
        ]);
    }

    public function registerMetaFields() {
        register_post_meta('fin_transaction', 'fin_amount', ['show_in_rest' => true, 'single' => true, 'type' => 'number']);
        register_post_meta('fin_transaction', 'fin_date', ['show_in_rest' => true, 'single' => true, 'type' => 'string']);
        register_post_meta('fin_transaction', 'fin_type', ['show_in_rest' => true, 'single' => true, 'type' => 'string']);
    }

    public function addCustomMetaBox() {
        add_meta_box('fin_transaction_details', 'Οικονομικά Στοιχεία', [$this, 'renderMetaBox'], 'fin_transaction', 'normal', 'high');
    }

    public function renderMetaBox($post) {
        $amount = get_post_meta($post->ID, 'fin_amount', true);
        $date   = get_post_meta($post->ID, 'fin_date', true);
        $type   = get_post_meta($post->ID, 'fin_type', true);

        wp_nonce_field('my_fin_save_meta_box_data', 'my_fin_meta_box_nonce');
        ?>
        <div style="display: flex; gap: 20px; margin-top: 10px;">
            <div><label><strong>Ποσό (€):</strong></label><br><input type="number" step="0.01" name="fin_amount" value="<?php echo esc_attr($amount); ?>" required style="width: 100%;"></div>
            <div><label><strong>Ημερομηνία:</strong></label><br><input type="date" name="fin_date" value="<?php echo esc_attr($date); ?>" required style="width: 100%;"></div>
            <div><label><strong>Τύπος:</strong></label><br>
                <select name="fin_type" required style="width: 100%;">
                    <option value="">Επίλεξε...</option>
                    <option value="income" <?php selected($type, 'income'); ?>>Έσοδο</option>
                    <option value="expense" <?php selected($type, 'expense'); ?>>Έξοδο</option>
                </select>
            </div>
        </div>
        <?php
    }
}