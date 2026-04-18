<?php
if (!defined('ABSPATH')) exit;

class AI_MH_Public {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_widget']);
        
        // Force HTTPS for all attachment URLs if is_ssl()
        add_filter('wp_get_attachment_url', [$this, 'force_https_urls']);
        add_filter('wp_get_attachment_image_src', [$this, 'force_https_image_src']);
    }

    public function force_https_urls($url) {
        if (is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        }
        return $url;
    }

    public function force_https_image_src($src) {
        if (is_ssl() && isset($src[0])) {
            $src[0] = str_replace('http://', 'https://', $src[0]);
        }
        return $src;
    }

    public function enqueue_assets() {
        wp_enqueue_style('akarai-cs-widget-css', AKARAI_CS_URL . 'public/widget.css', [], AKARAI_CS_VERSION);
        wp_enqueue_script('akarai-cs-widget-js', AKARAI_CS_URL . 'public/widget.js', [], AKARAI_CS_VERSION, true);

        $settings = get_option('akarai_cs_settings');
        if (!is_array($settings)) $settings = [];
        
        $lang_setting = $settings['widget_language'] ?? 'auto';
        $current_locale = get_locale();
        $is_tr = ($lang_setting === 'tr' || ($lang_setting === 'auto' && str_starts_with($current_locale, 'tr')));

        $i18n = [
            'start_chat' => __( 'Start Chat', 'akarai-customer-service' ),
            'name_placeholder' => __( 'Your Name', 'akarai-customer-service' ),
            'phone_placeholder' => __( 'Your Phone (e.g. 05xx ...)', 'akarai-customer-service' ),
            'agreement_text' => $settings['kvkk_text'] ?? __( 'I agree to the privacy policy', 'akarai-customer-service' ),
            'welcome_lead' => __( 'Please enter your information to start chatting:', 'akarai-customer-service' ),
            'input_placeholder' => __( 'Type your message...', 'akarai-customer-service' ),
            'error_msg' => __( 'Sorry, I cannot answer right now.', 'akarai-customer-service' ),
            'faq_title' => __( 'Frequently Asked Questions', 'akarai-customer-service' ),
        ];

        // Override if forced to Turkish or detected as Turkish in auto mode
        if ($is_tr) {
            $i18n = [
                'start_chat' => 'Sohbeti Başlat',
                'name_placeholder' => 'Adınız Soyadınız',
                'phone_placeholder' => 'Telefon Numaranız (05xx ...)',
                'agreement_text' => $settings['kvkk_text'] ?? 'Gizlilik politikasını kabul ediyorum',
                'welcome_lead' => 'Sohbete başlamak için lütfen bilgilerinizi girin:',
                'input_placeholder' => 'Mesajınızı yazın...',
                'error_msg' => 'Üzgünüm, şu an yanıt veremiyorum.',
                'faq_title' => 'Sıkça Sorulan Sorular',
            ];
        }

        wp_localize_script('akarai-cs-widget-js', 'akarai_cs_obj', [
            'rest_url' => esc_url_raw(set_url_scheme(rest_url('ai-mh/v1/'))),
            'bot_name' => $settings['bot_name'] ?? 'Agent AkarAi',
            'welcome_msg' => $settings['welcome_msg'] ?? ($is_tr ? 'Merhaba! Size nasıl yardımcı olabilirim?' : __( 'Hi! How can I help you today?', 'akarai-customer-service' )),
            'primary_color' => $settings['primary_color'] ?? '#2563eb',
            'position' => $settings['position'] ?? 'left',
            'faqs' => [
                $settings['faq_1'] ?? '',
                $settings['faq_2'] ?? '',
                $settings['faq_3'] ?? ''
            ],
            'i18n' => $i18n
        ]);
    }

    public function render_widget() {
        $settings = get_option('akarai_cs_settings');
        if (!is_array($settings)) $settings = [];
        
        $lang_setting = $settings['widget_language'] ?? 'auto';
        $current_locale = get_locale();
        $is_tr = ($lang_setting === 'tr' || ($lang_setting === 'auto' && str_starts_with($current_locale, 'tr')));

        $online_text = ($is_tr) ? 'Çevrimiçi' : __( 'Online', 'akarai-customer-service' );
        $form_text = ($is_tr) ? 'Lütfen başlamak için bilgilerinizi girin:' : __( 'Please enter your information to start:', 'akarai-customer-service' );
        $name_text = ($is_tr) ? 'Adınız Soyadınız' : __( 'Your Full Name', 'akarai-customer-service' );
        $phone_text = ($is_tr) ? 'Telefon Numaranız' : __( 'Your Phone Number', 'akarai-customer-service' );
        $send_text = ($is_tr) ? 'Gönder' : __( 'Send', 'akarai-customer-service' );
        ?>
        <div id="ai-mh-widget" class="ai-mh-position-<?php echo esc_attr($settings['position'] ?? 'left'); ?>">
            <div id="ai-mh-button" style="background-color: <?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>">
                <svg viewBox="0 0 24 24" fill="white" width="30px" height="30px"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
            </div>

            <div id="ai-mh-attention-bubble">
                <span id="ai-mh-attention-close">&times;</span>
                <?php echo esc_html($settings['welcome_msg'] ?? ($is_tr ? 'Merhaba! Size nasıl yardımcı olabilirim?' : __( 'Hi! How can I help you?', 'akarai-customer-service' ))); ?> 👋
            </div>

            <div id="ai-mh-chat-window" style="display: none;">
                <div class="ai-mh-header" style="background-color: <?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>">
                    <div class="ai-mh-header-img">
                        <?php if (!empty($settings['header_image_id'])): 
                            $img_html = wp_get_attachment_image($settings['header_image_id'], 'thumbnail');
                            echo set_url_scheme($img_html);
                        endif; ?>
                    </div>
                    <div class="ai-mh-header-info">
                        <span><?php echo esc_html($settings['bot_name'] ?? 'Agent AkarAi'); ?></span>
                        <small><?php echo esc_html($online_text); ?></small>
                    </div>
                    <span id="ai-mh-close">&times;</span>
                </div>
                
                <div id="ai-mh-messages">
                    <!-- Dynamic Messages -->
                </div>

                <div id="ai-mh-lead-form">
                    <p><?php echo esc_html($form_text); ?></p>
                    <input type="text" id="ai-mh-name" placeholder="<?php echo esc_attr($name_text); ?>">
                    <input type="text" id="ai-mh-phone" placeholder="<?php echo esc_attr($phone_text); ?>">
                    
                    <div class="ai-mh-agreement-area">
                        <label for="ai-mh-kvkk-check" class="ai-mh-agreement-label">
                            <input type="checkbox" id="ai-mh-kvkk-check">
                            <span class="ai-mh-agreement-text">
                                <?php if (!empty($settings['kvkk_url'])): ?>
                                    <a href="<?php echo esc_url($settings['kvkk_url']); ?>" target="_blank"><?php echo esc_html($settings['kvkk_text'] ?? ($is_tr ? 'Gizlilik politikasını kabul ediyorum' : __( 'I agree to the privacy policy', 'akarai-customer-service' ))); ?></a>
                                <?php else: ?>
                                    <?php echo esc_html($settings['kvkk_text'] ?? ($is_tr ? 'Gizlilik politikasını kabul ediyorum' : __( 'I agree to the privacy policy', 'akarai-customer-service' ))); ?>
                                <?php endif; ?>
                            </span>
                        </label>
                    </div>

                    <button id="ai-mh-start-chat" style="background-color: <?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>"><?php echo esc_html($is_tr ? 'Sohbeti Başlat' : __( 'Start Chat', 'akarai-customer-service' )); ?></button>
                </div>

                <div id="ai-mh-input-area" style="display: none;">
                    <input type="text" id="ai-mh-user-input" placeholder="<?php echo esc_attr($is_tr ? 'Mesajınızı yazın...' : __( 'Type your message...', 'akarai-customer-service' )); ?>">
                    <button id="ai-mh-send" title="<?php echo esc_attr($send_text); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
