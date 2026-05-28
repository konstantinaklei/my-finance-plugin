<?php
namespace MyFinance\Infrastructure\Persistence;

if (!defined('ABSPATH')) exit;

class MySQLTransactionRepository {
    
    public function registerHooks() {
        
        add_action('save_post_fin_transaction', [$this, 'saveMetaBoxData']);
        

        add_filter('wp_insert_post_data', [$this, 'autoTitleById'], 10, 2);
        add_action('save_post_fin_transaction', [$this, 'updateTitleAfterSave'], 10, 3);
    }

    public function saveMetaBoxData(int $post_id) {
        if (!isset($_POST['my_fin_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['my_fin_meta_box_nonce'])), 'my_fin_save_meta_box_data')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['fin_amount'])) update_post_meta($post_id, 'fin_amount', sanitize_text_field(wp_unslash($_POST['fin_amount'])));
        if (isset($_POST['fin_date'])) update_post_meta($post_id, 'fin_date', sanitize_text_field(wp_unslash($_POST['fin_date'])));
        if (isset($_POST['fin_type'])) update_post_meta($post_id, 'fin_type', sanitize_text_field(wp_unslash($_POST['fin_type'])));
    }
    public function autoTitleById(array $data, array $postarr) {
        if ($data['post_type'] == 'fin_transaction') {
            $data['post_title'] = empty($postarr['ID']) ? 'New Transaction' : '#' . $postarr['ID'];
        }
        return $data;
    }

    public function updateTitleAfterSave(int $post_id, \WP_Post $post, $update) {
        if (wp_is_post_revision($post_id)) return;
        $new_title = '#' . $post_id;
        
        if ($post->post_title !== $new_title) {
            remove_action('save_post_fin_transaction', [$this, 'updateTitleAfterSave']);
            wp_update_post(['ID' => $post_id, 'post_title' => $new_title]);
            add_action('save_post_fin_transaction', [$this, 'updateTitleAfterSave']);
        }
    }
}