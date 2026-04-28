<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Asian_Post_Photo_Card_Core
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_notices', array($this, 'display_activation_notice'));
        register_activation_hook(APC_PLUGIN_FILE, array($this, 'activate_plugin'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function activate_plugin()
    {
        $directories = array(
            APC_PATH . 'assets/css/',
            APC_PATH . 'assets/js/',
            APC_PATH . 'assets/templates/',
            APC_PATH . 'assets/images/',
            APC_PATH . 'assets/fonts/'
        );
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }

        // Set transient for admin notice
        set_transient('APC_activation_notice', true, 5);

        // Auto-create page with shortcode
        $page_title = 'Asian Post Photo Card';
        $page_content = '[photo_card]';

        $existing_pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'any',
            'title' => $page_title,
            'numberposts' => 1
        ));

        if (empty($existing_pages)) {
            wp_insert_post(array(
                'post_type'    => 'page',
                'post_title'   => $page_title,
                'post_content' => $page_content,
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ));
        }
    }

    public function display_activation_notice()
    {
        if (get_transient('APC_activation_notice')) {
?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Asian Post Photo Card Generator activated! A dedicated page has been created automatically. You can also use the shortcode [photo_card] to display the generator on any page or post.', 'asian-post-photo-card'); ?></p>
            </div>
<?php
            delete_transient('APC_activation_notice');
        }
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('asian-post-photo-card', false, dirname(plugin_basename((string) APC_PLUGIN_FILE)) . '/languages');
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('apc-style', APC_ASSETS_URL . 'css/apc-style.css', array(), APC_VERSION);
        wp_enqueue_style('apc-google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap', array(), null);

        wp_enqueue_script('apc-html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), '1.4.1', true);
        wp_enqueue_script('apc-qrcode-lib', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array(), '1.0.0', true);
        wp_enqueue_script('apc-script', APC_ASSETS_URL . 'js/apc-script.js', array('jquery', 'apc-html2canvas', 'apc-qrcode-lib'), APC_VERSION, true);

        wp_localize_script('apc-script', 'apcData', array(
            'ajaxurl'      => esc_url(admin_url('admin-ajax.php')),
            'nonce'        => wp_create_nonce('APC_ajax_nonce'),
            'templatesUrl' => esc_url(APC_ASSETS_URL . 'templates/'),
            'defaultImage' => esc_url(APC_ASSETS_URL . 'images/asian-post-image.png'),
            'fontsUrl'     => esc_url(APC_ASSETS_URL . 'fonts/'),
            'strings'      => array(
                'error'       => esc_html__('Error fetching data.', 'asian-post-photo-card'),
                'invalidUrl'  => esc_html__('Please enter a valid Asian Post URL.', 'asian-post-photo-card'),
                'copying'     => esc_html__('Copying...', 'asian-post-photo-card'),
                'copySuccess' => esc_html__('Copied!', 'asian-post-photo-card'),
                'copyError'   => esc_html__('Copy failed.', 'asian-post-photo-card'),
            )
        ));

        // Date Sync Logic (Inline)
        $custom_js = "
        jQuery(document).ready(function($){
            var dateInput = $('#apc-custom-date');
            var dateText = $('.apc-date-text');
            if(dateInput.length > 0) { dateText.text(dateInput.val()); }
            dateInput.on('input', function() { dateText.text($(this).val()); });
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        var newText = dateText.text();
                        if (dateInput.val() !== newText) { dateInput.val(newText); }
                    }
                });
            });
            if (dateText.length > 0) { observer.observe(dateText[0], { childList: true, characterData: true, subtree: true }); }
        });
        ";
        wp_add_inline_script('apc-script', $custom_js);
    }
}
