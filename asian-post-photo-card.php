<?php

/**
 * Plugin Name: Asian Post Photo Card
 * Description: Generate social media cards from Asian Post articles with Custom Title/Date support.
 * Version: 5.3.0
 * Author: Delwar
 * Author URI: https://www.delwarhossain.net/
 * Text Domain: asian-post-photo-card
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('APC_VERSION', '5.3.0');
define('APC_PLUGIN_FILE', __FILE__);
define('APC_URL', plugin_dir_url(__FILE__));
define('APC_PATH', plugin_dir_path(__FILE__));
define('APC_ASSETS_URL', APC_URL . 'assets/');

// Include Core Files
require_once APC_PATH . 'includes/class-apc-utils.php';
require_once APC_PATH . 'includes/class-apc-ajax.php';
require_once APC_PATH . 'includes/class-apc-shortcode.php';
require_once APC_PATH . 'includes/class-apc-core.php';

// Initialize Components
function apc_init_plugin()
{
    Asian_Post_Photo_Card_Core::get_instance();
    Asian_Post_Photo_Card_Ajax::init();
    Asian_Post_Photo_Card_Shortcode::init();
}
add_action('plugins_loaded', 'apc_init_plugin');
