<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Asian_Post_Photo_Card_Ajax
{
    public static function init()
    {
        add_action('wp_ajax_APC_get_external_post', array(__CLASS__, 'ajax_get_external_post'));
    }

    public static function ajax_get_external_post()
    {
        // 1. Verify Nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'APC_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed. Please refresh the page.', 'asian-post-photo-card')));
            exit;
        }

        // 2. Privilege validation: Ensure only allowed users (e.g. contributors+) can fetch data
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'asian-post-photo-card')));
            exit;
        }

        $url = isset($_POST['post_url']) ? esc_url_raw(wp_unslash($_POST['post_url'])) : '';
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => __('Please provide a valid URL.', 'asian-post-photo-card')));
        }

        $cache_key = 'APC_cache_' . md5($url);
        $cached_data = get_transient($cache_key);
        if (false !== $cached_data) {
            wp_send_json_success($cached_data);
        }

        $response = wp_safe_remote_get($url, array('timeout' => 30, 'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'));
        if (is_wp_error($response)) wp_send_json_error(array('message' => __('Failed to connect to the provided URL.', 'asian-post-photo-card')));

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) wp_send_json_error(array('message' => __('Empty response from server.', 'asian-post-photo-card')));

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        // Title
        $title = '';
        $nodes = $xpath->query('//meta[@property="og:title"]/@content');
        if ($nodes->length) {
            $title = $nodes->item(0)->nodeValue;
        } else {
            $nodes = $xpath->query('//title');
            if ($nodes->length) $title = $nodes->item(0)->nodeValue;
        }

        $title = preg_replace('/ \| .*$/', '', $title);
        $title = preg_replace('/ \- .*$/', '', $title);
        // Deep sanitization of the scraped outer title
        $title = sanitize_text_field(wp_unslash(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        // Language
        $language = 'english';
        if (preg_match('/[\x{0980}-\x{09FF}]/u', $title)) {
            $language = 'bangla';
        }

        // Image Extraction
        $image_url = Asian_Post_Photo_Card_Utils::extract_image_url($xpath, $url);

        // Base64 Image
        $base64_image = '';
        if (!empty($image_url)) {
            $img_response = wp_safe_remote_get($image_url, array('timeout' => 15));
            if (!is_wp_error($img_response) && wp_remote_retrieve_response_code($img_response) == 200) {
                $type = wp_remote_retrieve_header($img_response, 'content-type');
                if (empty($type)) $type = 'image/jpeg';
                $body = wp_remote_retrieve_body($img_response);
                $base64_image = 'data:' . $type . ';base64,' . base64_encode($body);
            }
        }

        // Date extraction
        $date_str = Asian_Post_Photo_Card_Utils::extract_universal_date($xpath, $language);

        // QR URL extraction via shortlink
        $qr_url = Asian_Post_Photo_Card_Utils::generate_qr_url($url, $response, $xpath);

        $data = array(
            // Use esc_html or wp_kses dynamically to guarantee valid raw response strings
            'title'     => wp_kses_post($title),
            'image'     => sanitize_text_field($base64_image),
            'date'      => sanitize_text_field($date_str),
            'language'  => sanitize_text_field($language),
            'qr_url'    => esc_url_raw($qr_url)
        );

        set_transient($cache_key, $data, HOUR_IN_SECONDS);
        wp_send_json_success($data);
    }
}
