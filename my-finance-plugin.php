<?php
/**
 * Plugin Name: My Finance Plugin
 * Description: Σύστημα Διαχείρισης Εσόδων - Εξόδων.
 * Version: 1.0
 * Author: Konstantina
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'fin_register_core_structures');

function fin_register_core_structures() {
    
    register_taxonomy('fin_category', 'fin_transaction', [
        'label'        => 'Κατηγορίες',
        'hierarchical' => true,
        'show_in_rest' => true, // Απαραίτητο για το REST API [cite: 39]
        'show_admin_column' => true,
    ]);

    
    register_post_type('fin_transaction', [
        'labels'      => ['name' => 'Συναλλαγές', 'singular_name' => 'Συναλλαγή'],
        'public'      => true,
        'show_in_rest' => true, // Επιτρέπει τη χρήση Fetch API [cite: 39]
        'menu_icon'   => 'dashicons-chart-line',
        'supports'    => ['title', 'editor', 'custom-fields'],
    ]);
}