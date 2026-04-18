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
        'labels'      => ['name' => 'Transactions', 'singular_name' => 'Transaction'],
        'public'      => true,
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
    register_post_meta('fin_transaction', 'fin_description', [
    'show_in_rest' => true,
    'single'       => true,
    'type'         => 'string',
    ]);
});