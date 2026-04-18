<?php
if (!defined('ABSPATH')) exit;

class AI_MH_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_ai_mh_scan_site', [$this, 'handle_scan_site']);
        add_action('wp_ajax_ai_mh_prepare_indexing', [$this, 'handle_prepare_indexing']);
        add_action('wp_ajax_ai_mh_index_batch', [$this, 'handle_index_batch']);
        add_action('wp_ajax_ai_mh_delete_index', [$this, 'handle_delete_index']);
        add_action('wp_ajax_ai_mh_get_index_content', [$this, 'handle_get_index_content']);
        add_action('wp_ajax_ai_mh_save_manual', [$this, 'handle_save_manual']);
    }

    public function add_menu() {
        add_menu_page(
            __( 'AkarAi Customer Service', 'akarai-customer-service' ),
            __( 'AkarAi', 'akarai-customer-service' ),
            'manage_options',
            'akarai-cs-settings',
            [$this, 'render_settings_page'],
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2563eb"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8 12c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>'),
            30
        );

        add_submenu_page(
            'akarai-cs-settings',
            __( 'General Settings', 'akarai-customer-service' ),
            __( 'Settings', 'akarai-customer-service' ),
            'manage_options',
            'akarai-cs-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'akarai-cs-settings',
            __( 'Site Indexing', 'akarai-customer-service' ),
            __( 'Indexing', 'akarai-customer-service' ),
            'manage_options',
            'akarai-cs-indexer',
            [$this, 'render_indexer_page']
        );

        add_submenu_page(
            'akarai-cs-settings',
            __( 'Chat History', 'akarai-customer-service' ),
            __( 'Chat History', 'akarai-customer-service' ),
            'manage_options',
            'akarai-cs-conversations',
            [$this, 'render_conversations_page']
        );

        add_submenu_page(
            'akarai-cs-settings',
            __( 'Analytics', 'akarai-customer-service' ),
            __( '📊 Analytics', 'akarai-customer-service' ),
            'manage_options',
            'akarai-cs-stats',
            [$this, 'render_stats_page']
        );
        
        add_submenu_page(
            'akarai-cs-settings',
            __( 'About', 'akarai-customer-service' ),
            __( 'ℹ️ About', 'akarai-customer-service' ),
            'manage_options',
            'akarai-cs-about',
            [$this, 'render_about_page']
        );
    }

    public function enqueue_assets($hook) {
        if (!in_array($hook, [
            'toplevel_page_akarai-cs-settings',
            'akarai_page_akarai-cs-indexer',
            'akarai_page_akarai-cs-conversations',
            'akarai_page_akarai-cs-stats'
        ])) return;
        
        wp_enqueue_media(); // For image selection
        wp_enqueue_style('akarai-cs-admin-css', AKARAI_CS_URL . 'admin/admin.css', [], AKARAI_CS_VERSION);
        wp_enqueue_script('akarai-cs-admin-js', AKARAI_CS_URL . 'admin/admin.js', ['jquery'], AKARAI_CS_VERSION, true);
        wp_localize_script('akarai-cs-admin-js', 'akarai_cs_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_mh_admin_nonce')
        ]);
    }

    public function render_settings_page() {
        $settings = get_option('akarai_cs_settings');
        if (!is_array($settings)) $settings = [];
        if (isset($_POST['akarai_cs_save_settings'])) {
            check_admin_referer('akarai_cs_save_settings_nonce');
            $settings = [
                'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
                'bot_name' => sanitize_text_field($_POST['bot_name'] ?? 'Agent AkarAi'),
                'welcome_msg' => sanitize_textarea_field($_POST['welcome_msg'] ?? ''),
                'primary_color' => sanitize_hex_color($_POST['primary_color'] ?? '#2563eb'),
                'model' => sanitize_text_field($_POST['model'] ?? 'gpt-3.5-turbo'),
                'system_prompt' => sanitize_textarea_field($_POST['system_prompt'] ?? ''),
                'position' => sanitize_text_field($_POST['position'] ?? 'left'),
                'header_image_id' => intval($_POST['header_image_id'] ?? 0),
                'kvkk_text' => sanitize_text_field($_POST['kvkk_text'] ?? __( 'I agree to the privacy policy', 'akarai-customer-service' )),
                'kvkk_url' => esc_url_raw($_POST['kvkk_url'] ?? ''),
                'widget_language' => sanitize_text_field($_POST['widget_language'] ?? 'auto'),
                'faq_1' => sanitize_text_field($_POST['faq_1'] ?? ''),
                'faq_2' => sanitize_text_field($_POST['faq_2'] ?? ''),
                'faq_3' => sanitize_text_field($_POST['faq_3'] ?? ''),
                'business_name' => sanitize_text_field($_POST['business_name'] ?? ''),
                'business_phone' => sanitize_text_field($_POST['business_phone'] ?? ''),
                'business_address' => sanitize_textarea_field($_POST['business_address'] ?? ''),
                'tone_of_voice' => sanitize_text_field($_POST['tone_of_voice'] ?? 'professional'),
                'personality_instructions' => sanitize_textarea_field($_POST['personality_instructions'] ?? ''),
                'custom_closure' => sanitize_text_field($_POST['custom_closure'] ?? '')
            ];
            update_option('akarai_cs_settings', $settings);
            echo '<div class="updated"><p>' . __( 'Settings saved.', 'akarai-customer-service' ) . '</p></div>';
        }

        include AKARAI_CS_PATH . 'admin/settings-page.php';
    }

    public function render_indexer_page() {
        global $wpdb;
        $table_knowledge = $wpdb->prefix . 'ai_mh_knowledge';
        $indexed_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_knowledge");
        $knowledge_base = $wpdb->get_results("SELECT * FROM $table_knowledge ORDER BY updated_at DESC");

        include AKARAI_CS_PATH . 'admin/indexer-page.php';
    }

    public function render_stats_page() {
        global $wpdb;
        $table_convs  = $wpdb->prefix . 'ai_mh_conversations';
        $table_leads  = $wpdb->prefix . 'ai_mh_leads';
        $table_msgs   = $wpdb->prefix . 'ai_mh_messages';

        $total_convs   = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_convs"));
        $total_leads   = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_leads"));
        $total_msgs    = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_msgs WHERE role = 'user'"));
        $total_prompt  = intval($wpdb->get_var("SELECT SUM(prompt_tokens) FROM $table_msgs"));
        $total_comp    = intval($wpdb->get_var("SELECT SUM(completion_tokens) FROM $table_msgs"));
        $total_tokens  = $total_prompt + $total_comp;

        // Estimated Cost
        $settings      = get_option('akarai_cs_settings');
        if (!is_array($settings)) $settings = [];
        $model         = $settings['model'] ?? 'gpt-3.5-turbo';
        $rate          = str_contains($model, 'gpt-4') ? 0.03 : 0.002;
        $est_cost      = round(($total_tokens / 1000) * $rate, 4);

        // Last 7 days lead trend
        $leads_trend = $wpdb->get_results(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $table_leads 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );

        // Last 7 days conversations
        $convs_trend = $wpdb->get_results(
            "SELECT DATE(started_at) as date, COUNT(*) as count 
             FROM $table_convs 
             WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(started_at)
             ORDER BY date ASC"
        );

        // Today
        $today_leads = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_leads WHERE DATE(created_at) = CURDATE()"));
        $today_convs = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_convs WHERE DATE(started_at) = CURDATE()"));

        include AKARAI_CS_PATH . 'admin/stats-page.php';
    }

    public function render_conversations_page() {
        global $wpdb;
        $table_convs = $wpdb->prefix . 'ai_mh_conversations';
        $table_leads = $wpdb->prefix . 'ai_mh_leads';
        $table_msgs  = $wpdb->prefix . 'ai_mh_messages';

        if (isset($_GET['conv_id'])) {
            $conv_id = intval($_GET['conv_id']);
            $conversation = $wpdb->get_row($wpdb->prepare(
                "SELECT c.*, l.name, l.surname, l.phone 
                 FROM $table_convs c 
                 JOIN $table_leads l ON c.lead_id = l.id 
                 WHERE c.id = %d", $conv_id
            ));
            $messages = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_msgs WHERE conversation_id = %d ORDER BY created_at ASC", $conv_id
            ));
            include AKARAI_CS_PATH . 'admin/conversation-detail.php';
        } else {
            $conversations = $wpdb->get_results(
                "SELECT c.*, l.name, l.surname, l.phone, 
                (SELECT count(*) FROM $table_msgs WHERE conversation_id = c.id) as msg_count 
                FROM $table_convs c 
                JOIN $table_leads l ON c.lead_id = l.id 
                ORDER BY c.started_at DESC"
            );
            include AKARAI_CS_PATH . 'admin/conversations-page.php';
        }
    }

    public function render_about_page() {
        include AKARAI_CS_PATH . 'admin/about-page.php';
    }

    public function handle_scan_site() {
        // Keep for legacy if needed, but JS will now use batching
        $this->handle_prepare_indexing();
    }

    public function handle_prepare_indexing() {
        check_ajax_referer('ai_mh_admin_nonce', 'nonce');
        global $wpdb;
        
        // Sanitize post types array
        $post_types = ['post', 'page'];
        if (isset($_POST['post_types']) && is_array($_POST['post_types'])) {
            $post_types = array_map('sanitize_text_field', $_POST['post_types']);
        }

        $is_full_scan = isset($_POST['full_scan']) && $_POST['full_scan'] === 'true';

        if ($is_full_scan) {
            // Use prepared query for security scan compliance
            $wpdb->query( $wpdb->prepare( 
                "DELETE FROM {$wpdb->prefix}ai_mh_knowledge WHERE post_id != %d", 
                0 
            ));
        }

        $posts = get_posts([
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        wp_send_json_success([
            'ids' => $posts,
            'total' => count($posts),
            'message' => sprintf( __( '%d items ready for indexing.', 'akarai-customer-service' ), count($posts) )
        ]);
    }

    public function handle_index_batch() {
        check_ajax_referer('ai_mh_admin_nonce', 'nonce');
        global $wpdb;
        $table_knowledge = $wpdb->prefix . 'ai_mh_knowledge';
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) wp_send_json_error( __( 'Invalid ID', 'akarai-customer-service' ) );

        $post = get_post($post_id);
        if (!$post) wp_send_json_error( __( 'Post not found', 'akarai-customer-service' ) );

        // Render content
        $is_deep_scan = isset($_POST['deep_scan']) && $_POST['deep_scan'] === 'true';
        $content = "";

        if ($is_deep_scan) {
            $url = get_permalink($post->ID);
            $response = wp_remote_get($url, ['timeout' => 20]);
            if (!is_wp_error($response)) {
                $html = wp_remote_retrieve_body($response);
                // Remove script and style elements
                $html = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $html);
                $content = strip_tags($html);
            }
        }

        if (empty($content)) {
            $content = apply_filters('the_content', $post->post_content);
            $content = strip_tags($content);
        }

        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (empty($content)) {
            wp_send_json_success(['message' => __( 'Empty content, skipped.', 'akarai-customer-service' )]);
        }

        // Cleanup existing
        $wpdb->delete($table_knowledge, ['post_id' => $post->ID]);

        // Chunk and Embed
        $chunks = $this->chunk_text($content);
        foreach ($chunks as $index => $chunk_text) {
            $suffix = (count($chunks) > 1) ? " (P" . ($index + 1) . ")" : "";
            $vector = AI_MH_API::get_embedding($chunk_text);
            
            $wpdb->insert($table_knowledge, [
                'post_id' => $post->ID,
                'post_title' => $post->post_title . $suffix,
                'content' => $chunk_text,
                'embedding' => $vector ? json_encode($vector) : null,
                'updated_at' => current_time('mysql')
            ]);
        }

        wp_send_json_success(['message' => sprintf( __( '%s indexed successfully.', 'akarai-customer-service' ), $post->post_title )]);
    }

    /**
     * Splits long text into chunks with overlap
     */
    private function chunk_text($text, $max_length = 1200, $overlap = 200) {
        if (mb_strlen($text) <= $max_length) {
            return [$text];
        }

        $chunks = [];
        $start = 0;
        $text_length = mb_strlen($text);

        while ($start < $text_length) {
            $end = $start + $max_length;
            
            if ($end < $text_length) {
                // Try to find a natural break near the end
                $sub = mb_substr($text, $start, $max_length);
                $last_period = mb_strrpos($sub, '.');
                
                if ($last_period !== false && $last_period > ($max_length * 0.7)) {
                    $end = $start + $last_period + 1;
                } else {
                    $last_space = mb_strrpos($sub, ' ');
                    if ($last_space !== false) {
                        $end = $start + $last_space;
                    }
                }
            }

            $chunk = trim(mb_substr($text, $start, $end - $start));
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }
            
            $start = $end - $overlap;
            if ($start < 0) $start = 0;
            if ($end >= $text_length) break;
        }

        return $chunks;
    }

    public function handle_get_index_content() {
        check_ajax_referer('ai_mh_admin_nonce', 'nonce');
        global $wpdb;
        $id = intval($_POST['id']);
        $table_knowledge = $wpdb->prefix . 'ai_mh_knowledge';
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_knowledge WHERE id = %d", $id));
        
        if ($item) {
            wp_send_json_success($item);
        } else {
            wp_send_json_error();
        }
    }

    public function handle_save_manual() {
        check_ajax_referer('ai_mh_admin_nonce', 'nonce');
        global $wpdb;
        $table_knowledge = $wpdb->prefix . 'ai_mh_knowledge';

        $id = intval($_POST['id'] ?? 0);
        $title = sanitize_text_field($_POST['title']);
        $content = sanitize_textarea_field($_POST['content']);

        // Generate Embedding for Manual Input
        $vector = AI_MH_API::get_embedding($content);

        $data = [
            'post_id' => 0,
            'post_title' => $title,
            'content' => $content,
            'embedding' => $vector ? json_encode($vector) : null,
            'updated_at' => current_time('mysql')
        ];

        if ($id > 0) {
            $wpdb->update($table_knowledge, $data, ['id' => $id]);
        } else {
            $wpdb->insert($table_knowledge, $data);
        }

        wp_send_json_success(['message' => __( 'Knowledge (and vectors) saved successfully.', 'akarai-customer-service' )]);
    }

    public function handle_delete_index() {
        check_ajax_referer('ai_mh_admin_nonce', 'nonce');
        global $wpdb;
        $id = intval($_POST['id']);
        $table_knowledge = $wpdb->prefix . 'ai_mh_knowledge';
        $wpdb->delete($table_knowledge, ['id' => $id]);
        wp_send_json_success(['message' => __( 'Content deleted from index.', 'akarai-customer-service' )]);
    }
}
