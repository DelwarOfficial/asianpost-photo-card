<?php
/**
 * IDE Helper Stubs
 * 
 * This file is purely for the code editor/IDE to recognize WordPress core functions 
 * and prevent "Undefined function" false positives. 
 */

// Prevent actual execution if accessed directly via browser
die('This file is for IDE indexing only.');

// Plugin Constants
/** @const string */
define('RPC_VERSION', '5.3.0');
/** @const string */
define('RPC_PLUGIN_FILE', 'asian-post-photo-card.php');
/** @const string */
define('RPC_URL', 'http://localhost');
/** @const string */
define('RPC_PATH', '/var/www');
/** @const string */
define('RPC_ASSETS_URL', 'http://localhost/assets');
/** @const int */
define('HOUR_IN_SECONDS', 3600);

// Utility
/** @return string */
function esc_url_raw($url, $protocols = null) { return ''; }
/** @return string */
function current_time($type, $gmt = 0) { return ''; }
/** @return string */
function sanitize_text_field($str) { return ''; }
/** @return string */
function wp_date($format, $timestamp = null, $timezone = null) { return ''; }
/** @return string */
function esc_attr($text) { return ''; }
/** @return void */
function esc_html_e($text, $domain = 'default') {}
/** @return string */
function esc_url($url, $protocols = null, $_context = 'display') { return ''; }
/** @return string */
function esc_html($text) { return ''; }
/** @return string */
function esc_html__($text, $domain = 'default') { return ''; }
/** @return string */
function __($text, $domain = 'default') { return ''; }
/** @return string */
function wp_create_nonce($action = -1) { return ''; }
/** @return int|false */
function wp_verify_nonce($nonce, $action = -1) { return false; }
/** @return string|array */
function wp_unslash($value) { return is_array($value) ? [] : ''; }
/** @return string */
function wp_kses_post($data) { return ''; }
/** @return bool */
function is_wp_error($thing) { return false; }

// Core
/** @return void */
function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {}
/** @return void */
function add_shortcode($tag, $callback) {}
/** @return array */
function shortcode_atts($pairs, $atts, $shortcode = '') { return []; }
/** @return void */
function register_activation_hook($file, $function) {}
/** @return string */
function plugin_dir_url($file) { return ''; }
/** @return string */
function plugin_dir_path($file) { return ''; }
/** @return string */
function plugin_basename($file) { return ''; }
/** @return bool */
function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) { return false; }
/** @return string */
function admin_url($path = '', $scheme = 'admin') { return ''; }

// File / Directory
/** @return bool */
function wp_mkdir_p($target) { return false; }

// Transients
/** @return bool */
function set_transient($transient, $value, $expiration = 0) { return false; }
/** @return mixed */
function get_transient($transient) { return false; }
/** @return bool */
function delete_transient($transient) { return false; }

// Posts
/** @return array */
function get_posts($args = null) { return []; }
/** @return int|\WP_Error */
function wp_insert_post($postarr, $wp_error = false, $fire_after_hooks = true) { return 0; }
/** @return bool */
function has_shortcode($content, $tag) { return false; }

// Assets
/** @return void */
function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {}
/** @return void */
function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {}
/** @return bool */
function wp_localize_script($handle, $object_name, $l10n) { return false; }
/** @return bool */
function wp_add_inline_script($handle, $data, $position = 'after') { return false; }

// HTTP / Remote
/** @return array|\WP_Error */
function wp_safe_remote_get($url, $args = array()) { return []; }
/** @return string */
function wp_remote_retrieve_body($response) { return ''; }
/** @return int|string */
function wp_remote_retrieve_response_code($response) { return 200; }
/** @return string|array */
function wp_remote_retrieve_header($response, $header) { return ''; }

// AJAX
/** @return void */
function wp_send_json_error($data = null, $status_code = null) {}
/** @return void */
function wp_send_json_success($data = null, $status_code = null) {}

// User Roles
/** @return bool */
function current_user_can($capability, ...$args) { return false; }

/**
 * Mock WP_Error Class
 */
class WP_Error {
    public function __construct($code = '', $message = '', $data = '') {}
    /** @return string */
    public function get_error_message($code = '') { return ''; }
    /** @return array */
    public function get_error_messages($code = '') { return []; }
    /** @return mixed */
    public function get_error_code() { return ''; }
}
