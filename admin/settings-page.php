<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap ai-mh-admin">
    <h1><?php _e( 'AkarAi Customer Service Settings', 'akarai-customer-service' ); ?></h1>
    <div class="ai-mh-grid">
        <div class="ai-mh-card">
            <h2><?php _e( 'General Settings', 'akarai-customer-service' ); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('akarai_cs_save_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e( 'OpenAI API Key', 'akarai-customer-service' ); ?></th>
                        <td>
                            <input type="password" name="api_key" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" class="regular-text" placeholder="sk-...">
                            <p class="description"><a href="https://platform.openai.com/api-keys" target="_blank"><?php _e( 'Get your API key here &rarr;', 'akarai-customer-service' ); ?></a></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Model Selection', 'akarai-customer-service' ); ?></th>
                        <td>
                            <select name="model">
                                <option value="gpt-3.5-turbo" <?php selected($settings['model'] ?? '', 'gpt-3.5-turbo'); ?>><?php _e( 'GPT-3.5 Turbo (Fast/Cheap)', 'akarai-customer-service' ); ?></option>
                                <option value="gpt-4o" <?php selected($settings['model'] ?? '', 'gpt-4o'); ?>><?php _e( 'GPT-4o (Smart/Performant)', 'akarai-customer-service' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Widget Language', 'akarai-customer-service' ); ?></th>
                        <td>
                            <select name="widget_language">
                                <option value="auto" <?php selected($settings['widget_language'] ?? 'auto', 'auto'); ?>><?php _e( 'Site Default', 'akarai-customer-service' ); ?></option>
                                <option value="en" <?php selected($settings['widget_language'] ?? 'auto', 'en'); ?>><?php _e( 'English', 'akarai-customer-service' ); ?></option>
                                <option value="tr" <?php selected($settings['widget_language'] ?? 'auto', 'tr'); ?>><?php _e( 'Turkish', 'akarai-customer-service' ); ?></option>
                            </select>
                            <p class="description"><?php _e( 'Override the frontend widget strings language.', 'akarai-customer-service' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Header Image', 'akarai-customer-service' ); ?></th>
                        <td>
                            <div class="ai-mh-image-preview mb-10">
                                <?php if (!empty($settings['header_image_id'])): 
                                    echo wp_get_attachment_image($settings['header_image_id'], 'thumbnail');
                                endif; ?>
                            </div>
                            <input type="hidden" name="header_image_id" id="ai-mh-header-image-id" value="<?php echo esc_attr($settings['header_image_id'] ?? ''); ?>">
                            <button type="button" class="button ai-mh-upload-btn"><?php _e( 'Select Image', 'akarai-customer-service' ); ?></button>
                            <button type="button" class="button ai-mh-remove-btn" <?php echo empty($settings['header_image_id']) ? 'style="display:none;"' : ''; ?>><?php _e( 'Remove', 'akarai-customer-service' ); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Bot Name', 'akarai-customer-service' ); ?></th>
                        <td><input type="text" name="bot_name" value="<?php echo esc_attr($settings['bot_name'] ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Welcome Message', 'akarai-customer-service' ); ?></th>
                        <td><textarea name="welcome_msg" class="large-text"><?php echo esc_textarea($settings['welcome_msg'] ?? ''); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Quick Questions (FAQ)', 'akarai-customer-service' ); ?></th>
                        <td>
                            <input type="text" name="faq_1" value="<?php echo esc_attr($settings['faq_1'] ?? ''); ?>" class="regular-text mb-10" placeholder="<?php esc_attr_e( 'Question 1', 'akarai-customer-service' ); ?>"><br>
                            <input type="text" name="faq_2" value="<?php echo esc_attr($settings['faq_2'] ?? ''); ?>" class="regular-text mb-10" placeholder="<?php esc_attr_e( 'Question 2', 'akarai-customer-service' ); ?>"><br>
                            <input type="text" name="faq_3" value="<?php echo esc_attr($settings['faq_3'] ?? ''); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Question 3', 'akarai-customer-service' ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Privacy Policy Text', 'akarai-customer-service' ); ?></th>
                        <td><input type="text" name="kvkk_text" value="<?php echo esc_attr($settings['kvkk_text'] ?? __( 'I agree to the privacy policy', 'akarai-customer-service' )); ?>" class="large-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Privacy Policy URL', 'akarai-customer-service' ); ?></th>
                        <td><input type="url" name="kvkk_url" value="<?php echo esc_url($settings['kvkk_url'] ?? ''); ?>" class="large-text" placeholder="https://your-site.com/privacy-policy"></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'System Prompt', 'akarai-customer-service' ); ?></th>
                        <td><textarea name="system_prompt" class="large-text" rows="5"><?php echo esc_textarea($settings['system_prompt'] ?? ''); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Primary Color', 'akarai-customer-service' ); ?></th>
                        <td><input type="color" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="akarai_cs_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'akarai-customer-service' ); ?>">
                </p>
            </form>
        </div>

        <div class="ai-mh-card">
            <h2><?php _e( 'Status', 'akarai-customer-service' ); ?></h2>
            <p><?php _e( 'The current status and usage summary of the plugin will be displayed here.', 'akarai-customer-service' ); ?></p>
        </div>
    </div>
</div>
