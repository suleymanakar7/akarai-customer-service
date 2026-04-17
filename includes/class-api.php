<?php
if (!defined('ABSPATH')) exit;

class AI_MH_API {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('ai-mh/v1', '/lead', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_lead'],
            'permission_callback' => '__return_true'
        ]);
        register_rest_route('ai-mh/v1', '/chat', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_chat'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function handle_lead($request) {
        global $wpdb;
        $params = $request->get_params();

        if (empty($params['name']) || empty($params['phone'])) {
            return new WP_Error('missing_fields', __( 'Missing required fields.', 'akarai-customer-service' ), ['status' => 400]);
        }

        $lang = $settings['widget_language'] ?? 'auto';
        $title = ($lang === 'tr') ? 'Ziyaretçi' : __( 'Visitor', 'akarai-customer-service' );

        if ($api_key) {
            $system_instr = 'You are a linguistic expert. Given a first name, determine the appropriate formal title (Mr., Ms., or Mx.) in English. Return ONLY the title word. If unsure, return "Visitor".';
            if ($lang === 'tr') {
                $system_instr = 'Sen bir dil uzmanısın. Verilen isme göre en uygun hitap şeklini (Bey, Hanım) Türkçe olarak belirle. Sadece tek bir kelime döndür. Emin değilsen "Ziyaretçi" döndür.';
            }

            $ai_response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => $system_instr],
                        ['role' => 'user', 'content' => 'Name: ' . $first_name]
                    ],
                    'max_tokens' => 10,
                    'temperature' => 0
                ]),
                'timeout' => 5
            ]);

            if (!is_wp_error($ai_response)) {
                $body = json_decode(wp_remote_retrieve_body($ai_response), true);
                $detected_title = trim($body['choices'][0]['message']['content'] ?? '');
                if (!empty($detected_title)) {
                    $title = $detected_title;
                }
            }
        }

        $table_leads = $wpdb->prefix . 'ai_mh_leads';
        $table_convs = $wpdb->prefix . 'ai_mh_conversations';

        $wpdb->insert($table_leads, [
            'name' => $name,
            'surname' => sanitize_text_field($params['surname'] ?? ''),
            'gender' => strtolower($title),
            'phone' => sanitize_text_field($params['phone']),
            'is_kvkk_accepted' => !empty($params['is_kvkk_accepted']) ? 1 : 0,
            'created_at' => current_time('mysql')
        ]);

        $lead_id = $wpdb->insert_id;

        $wpdb->insert($table_convs, [
            'lead_id' => $lead_id,
            'status' => 'active',
            'started_at' => current_time('mysql')
        ]);

        $conv_id = $wpdb->insert_id;
        
        if (!$lead_id || !$conv_id) {
            return new WP_Error('db_error', __( 'Database error occurred.', 'akarai-customer-service' ), ['status' => 500]);
        }

        if ($lang === 'tr') {
            $greeting = sprintf( 'Hoş geldiniz %s %s, size bugün nasıl yardımcı olabilirim?', $first_name, $title );
        } else {
            $greeting = sprintf( __( 'Welcome %s %s, how can I help you today?', 'akarai-customer-service' ), $first_name, $title );
        }
        
        $table_msgs = $wpdb->prefix . 'ai_mh_messages';
        $wpdb->insert($table_msgs, [
            'conversation_id' => $conv_id,
            'role' => 'assistant',
            'content' => $greeting,
            'created_at' => current_time('mysql')
        ]);

        return rest_ensure_response(['conv_id' => $conv_id, 'greeting' => $greeting]);
    }

    public function handle_chat($request) {
        global $wpdb;
        $params = $request->get_params();
        $conv_id = intval($params['conv_id']);
        $user_msg = sanitize_textarea_field($params['message']);
        
        $settings = get_option('akarai_cs_settings');
        $api_key = $settings['api_key'] ?? '';
        $table_knowledge = $wpdb->prefix . 'ai_mh_knowledge';

        if (!$api_key) {
            return new WP_Error('no_api_key', __( 'API key not found.', 'akarai-customer-service' ), ['status' => 500]);
        }

        // 1. Get Lead Info
        $table_convs = $wpdb->prefix . 'ai_mh_conversations';
        $table_leads = $wpdb->prefix . 'ai_mh_leads';
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT l.name FROM $table_leads l JOIN $table_convs c ON l.id = c.lead_id WHERE c.id = %d", $conv_id
        ));
        $lang = $settings['widget_language'] ?? 'auto';
        $user_name = $lead ? $lead->name : (($lang === 'tr') ? 'Ziyaretçi' : __( 'Visitor', 'akarai-customer-service' ));

        // 2. Save User Message
        $table_msgs = $wpdb->prefix . 'ai_mh_messages';
        $wpdb->insert($table_msgs, [
            'conversation_id' => $conv_id,
            'role' => 'user',
            'content' => $user_msg,
            'created_at' => current_time('mysql')
        ]);

        // 3. Always prioritize Manual Knowledge (post_id = 0)
        $manual_knowledge = $wpdb->get_results("SELECT post_title, content FROM $table_knowledge WHERE post_id = 0 LIMIT 3", ARRAY_A);
        
        // 4. Semantic Search logic
        $semantic_knowledge = [];
        $user_vector = self::get_embedding($user_msg);

        if ($user_vector) {
            $all_items = $wpdb->get_results("SELECT post_title, content, embedding FROM $table_knowledge WHERE embedding IS NOT NULL AND post_id != 0", ARRAY_A);
            
            $matches = [];
            foreach ($all_items as $item) {
                $item_vector = json_decode($item['embedding'], true);
                if (is_array($item_vector)) {
                    $score = self::cosine_similarity($user_vector, $item_vector);
                    if ($score > 0.3) {
                        $matches[] = [
                            'post_title' => $item['post_title'],
                            'content' => $item['content'],
                            'score' => $score
                        ];
                    }
                }
            }

            usort($matches, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $semantic_knowledge = array_slice($matches, 0, 7);
        }

        // 5. Fallback to Keyword Search if semantic fails
        if (empty($semantic_knowledge)) {
            $clean_msg = preg_replace('/[^\w\s]/u', '', $user_msg);
            $words = explode(' ', $clean_msg);
            $search_terms = [];
            $stop_words = ['what', 'how', 'about', 'info', 'give', 'can', 'which', 'where', 'for'];
            
            foreach ($words as $word) {
                $word = trim(mb_strtolower($word));
                if (mb_strlen($word) > 3 && !in_array($word, $stop_words)) {
                    $search_terms[] = '%' . $wpdb->esc_like($word) . '%';
                }
            }

            if (!empty($search_terms)) {
                $where_parts = [];
                $prepare_args = [];
                foreach ($search_terms as $term) {
                    $where_parts[] = "(post_title LIKE %s OR content LIKE %s)";
                    $prepare_args[] = $term;
                    $prepare_args[] = $term;
                }
                $where_clause = implode(' OR ', $where_parts);
                $query = "SELECT post_title, content FROM $table_knowledge WHERE post_id != 0 AND ($where_clause) LIMIT 8";
                $semantic_knowledge = $wpdb->get_results($wpdb->prepare($query, $prepare_args), ARRAY_A);
            }
        }

        // 6. Merge and Context Construction
        $all_knowledge = array_merge($manual_knowledge, $semantic_knowledge);
        $knowledge = [];
        $seen_titles = [];
        foreach ($all_knowledge as $item) {
            if (!in_array($item['post_title'], $seen_titles)) {
                $knowledge[] = $item;
                $seen_titles[] = $item['post_title'];
            }
        }

        $context = "";
        foreach ($knowledge as $item) {
            $context .= "Topic: " . $item['post_title'] . "\nInfo: " . $item['content'] . "\n---\n";
        }

        // 7. OpenAI Request
        $lang_instr = ($lang === 'tr') ? "IMPORTANT: Respond ONLY in Turkish." : "IMPORTANT: Respond in the language used by the user or force English if needed.";
        $system_prompt = ($settings['system_prompt'] ?? '') . "\n\n" . $lang_instr . "\nUser Name: " . $user_name . "\nCONVERSATION RULES:\n1. We have already greeted the user, do not say hello again.\n2. Do NOT use robotic patterns like 'Name: Answer'.\n3. Use the user's name naturally in the sentence.\n4. Speak as a professional customer service agent: polite, helpful, and concise.\n5. KEEP ANSWERS SHORT: Max 2-3 sentences. Only provide essential info.\n\nWEBSITE KNOWLEDGE (Answer ONLY based on this info, if unknown ask for contact details):\n" . $context;
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => $settings['model'] ?? 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $user_msg]
                ],
                'temperature' => 0.7
            ]),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            error_log('AKARAI_CS API Error: ' . $response->get_error_message());
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            error_log('AKARAI_CS OpenAI Error: ' . print_r($body['error'], true));
            return new WP_Error('openai_error', 'OpenAI: ' . ($body['error']['message'] ?? 'Unknown error'), ['status' => 500]);
        }

        $reply = $body['choices'][0]['message']['content'] ?? __( 'Sorry, I cannot answer right now.', 'akarai-customer-service' );
        $prompt_tokens      = intval($body['usage']['prompt_tokens'] ?? 0);
        $completion_tokens  = intval($body['usage']['completion_tokens'] ?? 0);

        // 8. Save Bot Reply & Stats
        $wpdb->insert($table_msgs, [
            'conversation_id'   => $conv_id,
            'role'              => 'assistant',
            'content'           => $reply,
            'prompt_tokens'     => $prompt_tokens,
            'completion_tokens' => $completion_tokens,
            'created_at'        => current_time('mysql')
        ]);

        return rest_ensure_response(['reply' => $reply]);
    }

    public static function get_embedding($text) {
        $settings = get_option('akarai_cs_settings');
        $api_key = $settings['api_key'] ?? '';
        if (empty($api_key)) return false;

        $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'text-embedding-3-small',
                'input' => $text
            ]),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) return false;
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['data'][0]['embedding'] ?? false;
    }

    public static function cosine_similarity($v1, $v2) {
        if (!is_array($v1) || !is_array($v2) || count($v1) !== count($v2)) return 0;
        $dot_product = 0; $norm1 = 0; $norm2 = 0;
        foreach ($v1 as $i => $val) {
            $dot_product += $val * $v2[$i];
            $norm1 += $val * $val;
            $norm2 += $v2[$i] * $v2[$i];
        }
        if ($norm1 == 0 || $norm2 == 0) return 0;
        return $dot_product / (sqrt($norm1) * sqrt($norm2));
    }
}
