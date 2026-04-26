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
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_notices', array($this, 'display_activation_notice'));
        register_activation_hook(RPC_PLUGIN_FILE, array($this, 'activate_plugin'));
    }

    public function activate_plugin()
    {
        $directories = array(
            RPC_PATH . 'assets/css/',
            RPC_PATH . 'assets/js/',
            RPC_PATH . 'assets/templates/',
            RPC_PATH . 'assets/images/',
            RPC_PATH . 'assets/fonts/'
        );
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }

        // Set transient for admin notice
        set_transient('rpc_activation_notice', true, 5);

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
                'post_author'  => 1,
            ));
        }
    }

    public function display_activation_notice()
    {
        if (get_transient('rpc_activation_notice')) {
?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Asian Post Photo Card Generator activated! A dedicated page has been created automatically. You can also use the shortcode [photo_card] to display the generator on any page or post.', 'rtv-photo-card'); ?></p>
            </div>
<?php
            delete_transient('rpc_activation_notice');
        }
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('rtv-photo-card', false, dirname(plugin_basename((string) RPC_PLUGIN_FILE)) . '/languages');
    }

    public function enqueue_assets()
    {
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'photo_card')) {
            return;
        }

        wp_enqueue_style('rpc-style', RPC_ASSETS_URL . 'css/tpc-style.css', array(), RPC_VERSION);
        wp_enqueue_style('rpc-google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap', array(), null);

        wp_enqueue_script('rpc-html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), '1.4.1', true);
        wp_enqueue_script('rpc-qrcode-lib', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array(), '1.0.0', true);
        wp_enqueue_script('rpc-script', RPC_ASSETS_URL . 'js/tpc-script.js', array('jquery', 'rpc-html2canvas', 'rpc-qrcode-lib'), RPC_VERSION, true);

        wp_localize_script('rpc-script', 'rpcData', array(
            'ajaxurl'      => esc_url(admin_url('admin-ajax.php')),
            'nonce'        => wp_create_nonce('rpc_ajax_nonce'),
            'templatesUrl' => esc_url(RPC_ASSETS_URL . 'templates/'),
            'defaultImage' => esc_url(RPC_ASSETS_URL . 'images/default-news.jpg'),
            'fontsUrl'     => esc_url(RPC_ASSETS_URL . 'fonts/'),
            'strings'      => array(
                'error'       => esc_html__('Error fetching data.', 'rtv-photo-card'),
                'invalidUrl'  => esc_html__('Please enter a valid Asian Post URL.', 'rtv-photo-card'),
                'copying'     => esc_html__('Copying...', 'rtv-photo-card'),
                'copySuccess' => esc_html__('Copied!', 'rtv-photo-card'),
                'copyError'   => esc_html__('Copy failed.', 'rtv-photo-card'),
            )
        ));

        // Date Sync Logic (Inline)
        $custom_js = "
        jQuery(document).ready(function($){
            var dateInput = $('#rpc-custom-date');
            var dateText = $('.rpc-date-text');
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
        wp_add_inline_script('rpc-script', $custom_js);
    }
}
