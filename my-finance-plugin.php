<?php
/**
 * Plugin Name: My Finance
 * Description: Σύστημα Διαχείρισης Εσόδων - Εξόδων (Clean Architecture).
 * Version: 1.2
 * Author: Konstantina
 */

if (!defined('ABSPATH')) exit;


spl_autoload_register(function ($class) {
    $prefix = 'MyFinance\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

add_action('plugins_loaded', function() {
    $wpSetup = new \MyFinance\Infrastructure\WordPressSetup();
    $wpSetup->registerHooks();

    $repository = new \MyFinance\Infrastructure\Persistence\MySQLTransactionRepository();
    $repository->registerHooks();
});