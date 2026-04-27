<?php
/**
 * Plugin Name: My Finance
 * Description: Σύστημα Διαχείρισης Εσόδων - Εξόδων.
 * Version: 1.0
 * Author: Konstantina
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'my_fin_register_core_structures');

function my_fin_register_core_structures() {
    
    register_taxonomy('fin_category', 'fin_transaction', [
        'label'        => 'Categories',
        'hierarchical' => true,
        'show_in_rest' => true, 
        'show_admin_column' => true,
    ]);

    
    register_post_type('fin_transaction', [
        'labels'      => [
            'name'          => 'Transactions', 
            'singular_name' => 'Transaction',
            'add_new'       => 'Add Transaction',     
            'add_new_item'  => 'Add New Transaction', 
            'all_items'     => 'All Transactions',    
            'search_items'  => 'Search Transactions'
        ],
        'public'      => true,
        'publicly_queryable' => false,
        'show_in_rest' => true,
        'menu_icon'   => 'dashicons-chart-line',
        'supports'    => ['title', 'editor', 'custom-fields'],
    ]);
}

add_action('init', function() {
    register_post_meta('fin_transaction', 'fin_amount', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'number',
    ]);
    register_post_meta('fin_transaction', 'fin_date', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ]);
    register_post_meta('fin_transaction', 'fin_type', [
    'show_in_rest' => true,
    'single'       => true,
    'type'         => 'string',
    ]);
});

add_filter('wp_insert_post_data', 'my_fin_auto_title_by_id', 10, 2);
function my_fin_auto_title_by_id($data, $postarr) {
    if ($data['post_type'] == 'fin_transaction') {
        if (empty($postarr['ID'])) {
            $data['post_title'] = 'New Transaction';
        } else {
            $data['post_title'] = '#' . $postarr['ID'];
        }
    }
    return $data;
}

add_action('save_post_fin_transaction', 'my_fin_update_title_after_save', 10, 3);
function my_fin_update_title_after_save($post_id, $post, $update) {
    if (wp_is_post_revision($post_id)) return;

    $new_title = '#' . $post_id;
    
    if ($post->post_title !== $new_title) {
        remove_action('save_post_fin_transaction', 'my_fin_update_title_after_save');
        
        wp_update_post([
            'ID'         => $post_id,
            'post_title' => $new_title
        ]);
        
        add_action('save_post_fin_transaction', 'my_fin_update_title_after_save');
    }
}