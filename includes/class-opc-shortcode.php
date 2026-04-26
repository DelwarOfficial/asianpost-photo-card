<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Asian_Post_Photo_Card_Shortcode
{
    public static function init()
    {
        add_shortcode('photo_card', array(__CLASS__, 'render_shortcode'));
    }

    public static function render_shortcode($atts = array())
    {
        $default_template = 'Default-Template-1080x1350.png';
        $files = glob(RPC_PATH . 'assets/templates/*.{png,jpg,jpeg}', GLOB_BRACE);
        if (!empty($files)) {
            $default_template = basename($files[0]);
        }
        $atts = shortcode_atts(array('template' => $default_template), $atts, 'photo_card');

        $bangla_date = wp_date('d F Y', null, new DateTimeZone('Asia/Dhaka'));
        $default_date_value = Asian_Post_Photo_Card_Utils::convert_to_bangla_date($bangla_date);

        // Generate nonce for frontend security
        $nonce_field = wp_create_nonce('rpc_ajax_nonce');

        ob_start();
?>
        <div id="rpc-wrapper" class="rpc-wrapper" data-nonce="<?php echo esc_attr($nonce_field); ?>">
            <div class="rpc-controls">

                <div class="rpc-section">
                    <h3 class="rpc-section-title">1. Article Settings</h3>
                    <div class="rpc-section-content">
                        <div class="rpc-form-group">
                            <label><?php esc_html_e('Article URL', 'rtv-photo-card'); ?></label>
                            <input id="rpc-url" type="url" class="rpc-input" placeholder="Paste URL here..." />
                        </div>
                    </div>
                </div>

                <div class="rpc-section">
                    <h3 class="rpc-section-title">2. Image Settings</h3>
                    <div class="rpc-section-content">
                        <div class="rpc-form-group">
                            <label><?php esc_html_e('Custom Image', 'rtv-photo-card'); ?></label>
                            <div class="rpc-file-input-wrapper">
                                <label for="rpc-custom-image-input" class="rpc-file-input-custom">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span id="rpc-file-name">Upload Image</span>
                                </label>
                                <input type="file" id="rpc-custom-image-input" class="rpc-file-input" accept="image/*" style="display:none;">
                            </div>
                        </div>

                        <div class="rpc-form-group">
                            <label style="display: flex; justify-content: space-between;">
                                <span><?php esc_html_e('Zoom:', 'rtv-photo-card'); ?></span>
                                <span id="rpc-image-scale-val" style="color:var(--rpc-primary);">1.0x</span>
                            </label>
                            <input type="range" id="rpc-image-scale" min="0.5" max="3" step="0.1" value="1" class="rpc-range-slider">
                        </div>

                        <div class="rpc-form-group">
                            <label><?php esc_html_e('Template', 'rtv-photo-card'); ?></label>
                            <select id="rpc-template" class="rpc-select">
                                <?php self::render_template_options($atts['template']); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rpc-section">
                    <h3 class="rpc-section-title">3. Text Settings</h3>
                    <div class="rpc-section-content">
                        <div class="rpc-form-group">
                            <label><?php esc_html_e('Font Style', 'rtv-photo-card'); ?></label>
                            <select id="rpc-font-family" class="rpc-select">
                                <option value="TiroBangla-Regular.woff2" selected>TiroBangla Regular (Default)</option>
                                <option value="GandhiSerif-Bold.woff2">Gandhi Serif Bold</option>
                                <!-- Future fonts can be added here easily -->
                            </select>
                        </div>

                        <div class="rpc-form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <label style="margin-bottom: 0;"><?php esc_html_e('Title', 'rtv-photo-card'); ?></label>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <label style="margin: 0; font-size: 13px; font-weight: normal; color: #666;">Size:</label>
                                    <input id="rpc-font-size" type="number" class="rpc-input" value="75" style="width: 70px; padding: 4px 8px; height: auto;" />
                                </div>
                            </div>
                            <div class="rpc-range-container">
                                <input type="range" id="rpc-font-size-range" min="30" max="120" value="75" class="rpc-range-slider">
                                <span id="rpc-font-size-val" class="rpc-range-val">75px</span>
                            </div>

                            <textarea id="rpc-custom-title" class="rpc-input" rows="2" placeholder="Title will appear here..."></textarea>

                            <small class="rpc-helper-text">
                                Tip: Use <b>*asterisks*</b> to highlight specific words. Example: <code>This is *important* news</code>
                            </small>
                        </div>

                        <div class="rpc-form-group">
                            <label><?php esc_html_e('Date', 'rtv-photo-card'); ?></label>
                            <input id="rpc-custom-date" type="text" class="rpc-input" value="<?php echo esc_attr($default_date_value); ?>" />
                        </div>
                    </div>
                </div>

                <div class="rpc-actions-container">
                    <button id="rpc-generate-btn" type="button" class="rpc-button rpc-primary" disabled>
                        <?php esc_html_e('Generate Card', 'rtv-photo-card'); ?>
                        <span class="rpc-spinner" style="display:none;">
                            <svg class="rpc-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>

                    <div class="rpc-secondary-actions">
                        <button id="rpc-copy" type="button" class="rpc-button rpc-dark" style="display:none;">
                            <?php esc_html_e('Copy', 'rtv-photo-card'); ?>
                        </button>
                        <button id="rpc-download" type="button" class="rpc-button rpc-success" style="display:none;">
                            <?php esc_html_e('Download', 'rtv-photo-card'); ?>
                        </button>
                        <button id="rpc-reset-btn" type="button" class="rpc-button rpc-outline">
                            <?php esc_html_e('Reset', 'rtv-photo-card'); ?>
                        </button>
                    </div>

                    <div id="rpc-message" class="rpc-message" style="display:none;"></div>
                </div>
            </div>

            <div class="rpc-preview-container-box">
                <div class="rpc-preview-wrapper" id="rpc-preview-wrapper">
                    <!-- The 1080x1350 card itself -->
                    <div id="rpc-card" class="rpc-card">
                        <img id="rpc-bg" src="<?php echo esc_url(RPC_ASSETS_URL . 'templates/' . $atts['template']); ?>" crossorigin="anonymous">

                        <div class="rpc-photo-container">
                            <img decoding="async" id="rpc-photo" crossorigin="anonymous" style="top: 124.444px; left: 22.2222px; transform: scale(1); cursor: grab;">
                        </div>

                        <div id="rpc-title-area" class="rpc-title-area"></div>

                        <div id="rpc-date-pill" class="rpc-date-pill">
                            <span class="rpc-date-text"><?php echo esc_html($default_date_value); ?></span>
                        </div>

                        <div id="rpc-qr" class="rpc-qr"></div>
                    </div>
                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }

    private static function render_template_options($default_template)
    {
        $files = glob(RPC_PATH . 'assets/templates/*.{png,jpg,jpeg}', GLOB_BRACE);
        if (empty($files)) {
            echo '<option value="">' . esc_html__('No templates found', 'rtv-photo-card') . '</option>';
            return;
        }
        foreach ($files as $file) {
            $filename = basename($file);
            $selected = ($filename === $default_template) ? 'selected' : '';
            echo '<option value="' . esc_attr($filename) . '" ' . esc_attr($selected) . '>' . esc_html($filename) . '</option>';
        }
    }
}
