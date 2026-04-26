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

define('RPC_VERSION', '5.3.0');
define('RPC_PLUGIN_FILE', __FILE__);
define('RPC_URL', plugin_dir_url(__FILE__));
define('RPC_PATH', plugin_dir_path(__FILE__));
define('RPC_ASSETS_URL', RPC_URL . 'assets/');

// Include Core Files
require_once RPC_PATH . 'includes/class-opc-utils.php';
require_once RPC_PATH . 'includes/class-opc-ajax.php';
require_once RPC_PATH . 'includes/class-opc-shortcode.php';
require_once RPC_PATH . 'includes/class-opc-core.php';

// Initialize Components
function rpc_init_plugin()
{
    Asian_Post_Photo_Card_Core::get_instance();
    Asian_Post_Photo_Card_Ajax::init();
    Asian_Post_Photo_Card_Shortcode::init();
}
add_action('plugins_loaded', 'rpc_init_plugin');
