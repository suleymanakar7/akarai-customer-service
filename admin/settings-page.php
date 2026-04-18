<?php if (!defined('ABSPATH')) exit; ?>

<div class="ai-mh-admin-wrap">
    <div class="ai-mh-header">
        <h1><?php _e( 'AkarAi Customer Service', 'akarai-customer-service' ); ?></h1>
        <div class="ai-mh-version">v<?php echo AKARAI_CS_VERSION; ?></div>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('akarai_cs_save_settings_nonce'); ?>
        
        <div class="ai-mh-dashboard">
            <!-- Sidebar Navigation -->
            <div class="ai-mh-nav">
                <div class="ai-mh-nav-item active" data-tab="tab-general">
                    <span class="dashicons dashicons-admin-generic"></span> <?php _e( 'General Settings', 'akarai-customer-service' ); ?>
                </div>
                <div class="ai-mh-nav-item" data-tab="tab-persona">
                    <span class="dashicons dashicons-admin-users"></span> <?php _e( 'AI Persona', 'akarai-customer-service' ); ?>
                </div>
                <div class="ai-mh-nav-item" data-tab="tab-business">
                    <span class="dashicons dashicons-store"></span> <?php _e( 'Business Profile', 'akarai-customer-service' ); ?>
                </div>
                <div class="ai-mh-nav-item" data-tab="tab-interface">
                    <span class="dashicons dashicons-art"></span> <?php _e( 'Interface & UI', 'akarai-customer-service' ); ?>
                </div>
                <div class="ai-mh-nav-item" data-tab="tab-privacy">
                    <span class="dashicons dashicons-shield"></span> <?php _e( 'Privacy & KVKK', 'akarai-customer-service' ); ?>
                </div>

                <div class="ai-mh-status-card">
                    <h3><?php _e( 'System Status', 'akarai-customer-service' ); ?></h3>
                    <p>● <?php echo !empty($settings['api_key']) ? '<span style="color:#16a34a">'.__('API Key Connected', 'akarai-customer-service').'</span>' : '<span style="color:#ef4444">'.__('API Key Missing', 'akarai-customer-service').'</span>'; ?></p>
                    <p>● <?php _e('Model:', 'akarai-customer-service'); ?> <strong><?php echo esc_html($settings['model'] ?? 'GPT-3.5'); ?></strong></p>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="ai-mh-content">
                
                <!-- TAB: General Settings -->
                <div id="tab-general" class="ai-mh-tab-content active">
                    <h2 class="ai-mh-section-title"><?php _e( 'General Settings', 'akarai-customer-service' ); ?></h2>
                    
                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'OpenAI API Key', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="password" name="api_key" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" placeholder="sk-...">
                            <p class="ai-mh-description"><a href="https://platform.openai.com/api-keys" target="_blank"><?php _e( 'Get your API key here &rarr;', 'akarai-customer-service' ); ?></a></p>
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Model Selection', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <select name="model">
                                <option value="gpt-3.5-turbo" <?php selected($settings['model'] ?? '', 'gpt-3.5-turbo'); ?>><?php _e( 'GPT-3.5 Turbo (Fast & Cheap)', 'akarai-customer-service' ); ?></option>
                                <option value="gpt-4o" <?php selected($settings['model'] ?? '', 'gpt-4o'); ?>><?php _e( 'GPT-4o (Smart & High Quality)', 'akarai-customer-service' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Widget Language', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <select name="widget_language">
                                <option value="auto" <?php selected($settings['widget_language'] ?? 'auto', 'auto'); ?>><?php _e( 'Detect Automatically (Site Default)', 'akarai-customer-service' ); ?></option>
                                <option value="en" <?php selected($settings['widget_language'] ?? 'auto', 'en'); ?>><?php _e( 'Force English', 'akarai-customer-service' ); ?></option>
                                <option value="tr" <?php selected($settings['widget_language'] ?? 'auto', 'tr'); ?>><?php _e( 'Force Turkish', 'akarai-customer-service' ); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- TAB: AI Persona -->
                <div id="tab-persona" class="ai-mh-tab-content">
                    <h2 class="ai-mh-section-title"><?php _e( 'AI Persona & Communication', 'akarai-customer-service' ); ?></h2>
                    
                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Bot Name', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="text" name="bot_name" value="<?php echo esc_attr($settings['bot_name'] ?? ''); ?>" placeholder="e.g. AkarAi Assistant">
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Welcome Message', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <textarea name="welcome_msg" rows="3"><?php echo esc_textarea($settings['welcome_msg'] ?? ''); ?></textarea>
                            <p class="ai-mh-description"><?php _e( 'First message shown to users when they open the chat.', 'akarai-customer-service' ); ?></p>
                        </div>
                    </div>

                    <div class="ai-mh-flex-row">
                        <div class="ai-mh-form-group">
                            <label class="ai-mh-label"><?php _e( 'Tone of Voice', 'akarai-customer-service' ); ?></label>
                            <div class="ai-mh-input-wrap">
                                <select name="tone_of_voice">
                                    <option value="professional" <?php selected($settings['tone_of_voice'] ?? 'professional', 'professional'); ?>><?php _e( 'Professional', 'akarai-customer-service' ); ?></option>
                                    <option value="friendly" <?php selected($settings['tone_of_voice'] ?? 'professional', 'friendly'); ?>><?php _e( 'Friendly & Samimi', 'akarai-customer-service' ); ?></option>
                                    <option value="boutique" <?php selected($settings['tone_of_voice'] ?? 'professional', 'boutique'); ?>><?php _e( 'Boutique & Warm', 'akarai-customer-service' ); ?></option>
                                    <option value="minimalist" <?php selected($settings['tone_of_voice'] ?? 'professional', 'minimalist'); ?>><?php _e( 'Minimalist & Direct', 'akarai-customer-service' ); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Detailed Persona Instructions', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <textarea name="personality_instructions" rows="4" placeholder="<?php esc_attr_e( 'How should the AI behave? e.g. Be like a helpful teacher, use nature emojis 🍀🌳...', 'akarai-customer-service' ); ?>"><?php echo esc_textarea($settings['personality_instructions'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Advanced System Prompt Override', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <textarea name="system_prompt" rows="3" placeholder="<?php esc_attr_e( 'Advanced OpenAI system instructions...', 'akarai-customer-service' ); ?>"><?php echo esc_textarea($settings['system_prompt'] ?? ''); ?></textarea>
                            <p class="ai-mh-description"><?php _e( 'Manual override for the core system prompt (Advanced).', 'akarai-customer-service' ); ?></p>
                        </div>
                    </div>
                </div>

                <!-- TAB: Business Profile -->
                <div id="tab-business" class="ai-mh-tab-content">
                    <h2 class="ai-mh-section-title"><?php _e( 'Business Profile', 'akarai-customer-service' ); ?></h2>
                    
                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Business Name', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="text" name="business_name" value="<?php echo esc_attr($settings['business_name'] ?? ''); ?>" placeholder="e.g. Aren Akademi">
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Contact Phone', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="text" name="business_phone" value="<?php echo esc_attr($settings['business_phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Office Address', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <textarea name="business_address" rows="2"><?php echo esc_textarea($settings['business_address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Custom Invitation / Signature', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="text" name="custom_closure" value="<?php echo esc_attr($settings['custom_closure'] ?? ''); ?>" placeholder="e.g. Sizi çayımızı içmeye davet ediyoruz. 🍀🌳">
                            <p class="ai-mh-description"><?php _e( 'Used when AI redirects users to contact the team.', 'akarai-customer-service' ); ?></p>
                        </div>
                    </div>
                </div>

                <!-- TAB: Interface -->
                <div id="tab-interface" class="ai-mh-tab-content">
                    <h2 class="ai-mh-section-title"><?php _e( 'Interface & UI Settings', 'akarai-customer-service' ); ?></h2>
                    
                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Primary Brand Color', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="color" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>" style="height:40px; width:100px;">
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Header Image', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <div class="ai-mh-image-preview">
                                <?php if (!empty($settings['header_image_id'])): 
                                    echo wp_get_attachment_image($settings['header_image_id'], 'thumbnail');
                                endif; ?>
                            </div>
                            <input type="hidden" name="header_image_id" id="ai-mh-header-image-id" value="<?php echo esc_attr($settings['header_image_id'] ?? ''); ?>">
                            <div class="mt-10">
                                <button type="button" class="button ai-mh-upload-btn"><?php _e( 'Select Image', 'akarai-customer-service' ); ?></button>
                                <button type="button" class="button ai-mh-remove-btn" <?php echo empty($settings['header_image_id']) ? 'style="display:none;"' : ''; ?>><?php _e( 'Remove', 'akarai-customer-service' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Quick Questions (FAQ)', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="text" name="faq_1" value="<?php echo esc_attr($settings['faq_1'] ?? ''); ?>" class="mb-10" placeholder="<?php esc_attr_e( 'Question 1', 'akarai-customer-service' ); ?>">
                            <input type="text" name="faq_2" value="<?php echo esc_attr($settings['faq_2'] ?? ''); ?>" class="mb-10" placeholder="<?php esc_attr_e( 'Question 2', 'akarai-customer-service' ); ?>">
                            <input type="text" name="faq_3" value="<?php echo esc_attr($settings['faq_3'] ?? ''); ?>" placeholder="<?php esc_attr_e( 'Question 3', 'akarai-customer-service' ); ?>">
                        </div>
                    </div>
                </div>

                <!-- TAB: Privacy -->
                <div id="tab-privacy" class="ai-mh-tab-content">
                    <h2 class="ai-mh-section-title"><?php _e( 'Privacy & KVKK Compliance', 'akarai-customer-service' ); ?></h2>
                    
                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Consent Text', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="text" name="kvkk_text" value="<?php echo esc_attr($settings['kvkk_text'] ?? __( 'I agree to the privacy policy', 'akarai-customer-service' )); ?>">
                        </div>
                    </div>

                    <div class="ai-mh-form-group">
                        <label class="ai-mh-label"><?php _e( 'Privacy Policy URL', 'akarai-customer-service' ); ?></label>
                        <div class="ai-mh-input-wrap">
                            <input type="url" name="kvkk_url" value="<?php echo esc_url($settings['kvkk_url'] ?? ''); ?>" placeholder="https://site.com/privacy-policy">
                        </div>
                    </div>
                </div>

                <div class="ai-mh-footer mt-20">
                    <input type="submit" name="akarai_cs_save_settings" class="ai-mh-save-btn" value="<?php esc_attr_e( 'Save All Settings', 'akarai-customer-service' ); ?>">
                </div>

            </div>
        </div>
    </form>
</div>
