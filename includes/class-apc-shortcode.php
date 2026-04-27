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
        $files = glob(APC_PATH . 'assets/templates/*.{png,jpg,jpeg}', GLOB_BRACE);
        if (!empty($files)) {
            $default_template = basename($files[0]);
        }
        $atts = shortcode_atts(array('template' => $default_template), $atts, 'photo_card');

        $bangla_date = wp_date('d F Y', null, new DateTimeZone('Asia/Dhaka'));
        $default_date_value = Asian_Post_Photo_Card_Utils::convert_to_bangla_date($bangla_date);

        // Generate nonce for frontend security
        $nonce_field = wp_create_nonce('APC_ajax_nonce');

        ob_start();
?>
        <div id="apc-wrapper" class="apc-wrapper" data-nonce="<?php echo esc_attr($nonce_field); ?>">
            <div class="apc-controls">

                <div class="apc-section">
                    <h3 class="apc-section-title">1. Article Settings</h3>
                    <div class="apc-section-content">
                        <div class="apc-form-group">
                            <label><?php esc_html_e('Article URL', 'asian-post-photo-card'); ?></label>
                            <input id="apc-url" type="url" class="apc-input" placeholder="Paste URL here..." />
                        </div>
                        <div class="apc-form-group apc-checkbox-group" style="display: flex; align-items: center; gap: 8px; margin-top: 15px;">
                            <input type="checkbox" id="apc-toggle-qr" class="apc-checkbox">
                            <label for="apc-toggle-qr" style="margin: 0; cursor: pointer;"><?php esc_html_e('Show QR Code', 'asian-post-photo-card'); ?></label>
                        </div>
                    </div>
                </div>

                <div class="apc-section">
                    <h3 class="apc-section-title">2. Image Settings</h3>
                    <div class="apc-section-content">
                        <div class="apc-form-group">
                            <label><?php esc_html_e('Custom Image', 'asian-post-photo-card'); ?></label>
                            <div class="apc-file-input-wrapper">
                                <label for="apc-custom-image-input" class="apc-file-input-custom">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span id="apc-file-name">Upload Image</span>
                                </label>
                                <input type="file" id="apc-custom-image-input" class="apc-file-input" accept="image/*" style="display:none;">
                            </div>
                        </div>

                        <div class="apc-form-group">
                            <label style="display: flex; justify-content: space-between;">
                                <span><?php esc_html_e('Zoom:', 'asian-post-photo-card'); ?></span>
                                <span id="apc-image-scale-val" style="color:var(--apc-primary);">1.0x</span>
                            </label>
                            <input type="range" id="apc-image-scale" min="0.5" max="3" step="0.1" value="1" class="apc-range-slider">
                        </div>

                        <div class="apc-form-group">
                            <label><?php esc_html_e('Template', 'asian-post-photo-card'); ?></label>
                            <select id="apc-template" class="apc-select">
                                <?php self::render_template_options($atts['template']); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="apc-section">
                    <h3 class="apc-section-title">3. Text Settings</h3>
                    <div class="apc-section-content">
                        <div class="apc-form-group">
                            <label><?php esc_html_e('Font Style', 'asian-post-photo-card'); ?></label>
                            <select id="apc-font-family" class="apc-select">
                                <option value="TiroBangla-Regular.woff2" selected>TiroBangla Regular (Default)</option>
                                <option value="GandhiSerif-Bold.woff2">Gandhi Serif Bold</option>
                                <!-- Future fonts can be added here easily -->
                            </select>
                        </div>

                        <div class="apc-form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <label style="margin-bottom: 0;"><?php esc_html_e('Title', 'asian-post-photo-card'); ?></label>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <label style="margin: 0; font-size: 13px; font-weight: normal; color: #666;">Size:</label>
                                    <input id="apc-font-size" type="number" class="apc-input" value="75" style="width: 70px; padding: 4px 8px; height: auto;" />
                                </div>
                            </div>
                            <div class="apc-range-container">
                                <input type="range" id="apc-font-size-range" min="30" max="120" value="75" class="apc-range-slider">
                                <span id="apc-font-size-val" class="apc-range-val">75px</span>
                            </div>

                            <textarea id="apc-custom-title" class="apc-input" rows="2" placeholder="Title will appear here..."></textarea>

                            <small class="apc-helper-text">
                                Tip: Use <b>*asterisks*</b> to highlight specific words. Example: <code>This is *important* news</code>
                            </small>
                            <div class="apc-checkbox-group" style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                <input type="checkbox" id="apc-toggle-highlight" class="apc-checkbox">
                                <label for="apc-toggle-highlight" style="margin: 0; cursor: pointer;"><?php esc_html_e('Enable Highlight', 'asian-post-photo-card'); ?></label>
                            </div>
                        </div>

                        <div class="apc-form-group">
                            <label><?php esc_html_e('Date', 'asian-post-photo-card'); ?></label>
                            <input id="apc-custom-date" type="text" class="apc-input" value="<?php echo esc_attr($default_date_value); ?>" />
                        </div>
                    </div>
                </div>

                <div class="apc-actions-container">
                    <button id="apc-generate-btn" type="button" class="apc-button apc-primary" disabled>
                        <?php esc_html_e('Generate Card', 'asian-post-photo-card'); ?>
                        <span class="apc-spinner" style="display:none;">
                            <svg class="apc-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>

                    <div class="apc-secondary-actions">
                        <button id="apc-copy" type="button" class="apc-button apc-dark" style="display:none;">
                            <?php esc_html_e('Copy', 'asian-post-photo-card'); ?>
                        </button>
                        <button id="apc-download" type="button" class="apc-button apc-success" style="display:none;">
                            <?php esc_html_e('Download', 'asian-post-photo-card'); ?>
                        </button>
                        <button id="apc-reset-btn" type="button" class="apc-button apc-outline">
                            <?php esc_html_e('Reset', 'asian-post-photo-card'); ?>
                        </button>
                    </div>

                    <div id="apc-message" class="apc-message" style="display:none;"></div>
                </div>
            </div>

            <div class="apc-preview-container-box">
                <div class="apc-preview-wrapper" id="apc-preview-wrapper">
                    <!-- The 1080x1350 card itself -->
                    <div id="apc-card" class="apc-card">
                        <img id="apc-bg" src="<?php echo esc_url(APC_ASSETS_URL . 'templates/' . $atts['template']); ?>" crossorigin="anonymous">

                        <div class="apc-photo-container">
                            <img decoding="async" id="apc-photo" crossorigin="anonymous" style="top: 276.717px; left: 22.2222px; transform: scale(1); cursor: grab;">
                        </div>

                        <div id="apc-title-area" class="apc-title-area"></div>

                        <div id="apc-date-pill" class="apc-date-pill">
                            <span class="apc-date-text"><?php echo esc_html($default_date_value); ?></span>
                        </div>

                        <div id="apc-qr" class="apc-qr"></div>
                    </div>
                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }

    private static function render_template_options($default_template)
    {
        $files = glob(APC_PATH . 'assets/templates/*.{png,jpg,jpeg}', GLOB_BRACE);
        if (empty($files)) {
            echo '<option value="">' . esc_html__('No templates found', 'asian-post-photo-card') . '</option>';
            return;
        }
        foreach ($files as $file) {
            $filename = basename($file);
            $selected = ($filename === $default_template) ? 'selected' : '';
            echo '<option value="' . esc_attr($filename) . '" ' . esc_attr($selected) . '>' . esc_html($filename) . '</option>';
        }
    }
}
