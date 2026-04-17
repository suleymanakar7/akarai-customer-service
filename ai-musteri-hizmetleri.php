<?php
/**
 * Plugin Name: AkarAi Customer Service
 * Description: Professional AI-powered customer service widget with Semantic Search (RAG), Token Analytics, and Lead Generation.
 * Version: 1.0.3
 * Author: Akarca Yazılım
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: akarai-customer-service
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AKARAI_CS_PATH', plugin_dir_path(__FILE__));
define('AKARAI_CS_URL', plugin_dir_url(__FILE__));
define('AKARAI_CS_VERSION', '1.0.3');

// Backward compatibility or internal usage alias
if (!defined('AI_MH_VERSION')) define('AI_MH_VERSION', AKARAI_CS_VERSION);
if (!defined('AI_MH_PATH')) define('AI_MH_PATH', AKARAI_CS_PATH);
if (!defined('AI_MH_URL')) define('AI_MH_URL', AKARAI_CS_URL);

// Include requirements
require_once AKARAI_CS_PATH . 'includes/class-db.php';
require_once AKARAI_CS_PATH . 'includes/class-admin.php';
require_once AKARAI_CS_PATH . 'includes/class-public.php';
require_once AKARAI_CS_PATH . 'includes/class-api.php';

/**
 * Activation Hook
 */
register_activation_hook(__FILE__, 'akarai_cs_activate');
function akarai_cs_activate() {
    $db = new AI_MH_DB();
    $db->create_tables();
    
    // Default settings (English by default for Global Reach)
    if (!get_option('akarai_cs_settings')) {
        update_option('akarai_cs_settings', [
            'bot_name' => 'Agent AkarAi',
            'welcome_msg' => 'Hi! How can I help you today?',
            'primary_color' => '#2563eb',
            'model' => 'gpt-3.5-turbo',
            'system_prompt' => 'You are a professional customer service agent. Only provide information based on the website content.',
            'position' => 'left',
            'kvkk_text' => 'I agree to the privacy policy',
            'faq_1' => 'What services do you offer?',
            'faq_2' => 'How can I contact you?',
            'faq_3' => 'Tell me more about your company.'
        ]);
        
        // Migrate old settings if exist
        $old = get_option('ai_mh_settings');
        if ($old) update_option('akarai_cs_settings', $old);
    }
}

/**
 * Initialize Plugin
 */
function akarai_cs_init() {
    // Load Text Domain
    load_plugin_textdomain('akarai-customer-service', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Auto Update DB if version changed
    if (get_option('akarai_cs_version') !== AKARAI_CS_VERSION) {
        $db = new AI_MH_DB();
        $db->create_tables();
        update_option('akarai_cs_version', AKARAI_CS_VERSION);
    }

    new AI_MH_Admin();
    new AI_MH_Public();
    new AI_MH_API();
}
add_action('plugins_loaded', 'akarai_cs_init');
