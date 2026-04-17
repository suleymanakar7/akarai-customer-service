<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap ai-mh-admin">
    <h1><?php _e( 'About AkarAi Customer Service', 'akarai-customer-service' ); ?></h1>
    
    <div class="ai-mh-grid" style="grid-template-columns: 1fr;">
        <div class="ai-mh-card mb-20">
            <div style="display: flex; align-items: flex-start; gap: 30px;">
                <div style="flex-shrink: 0; background: #f0f4ff; padding: 20px; border-radius: 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2563eb" width="80" height="80"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8 12c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                </div>
                <div>
                    <h2 style="border:none; padding:0; margin-bottom: 10px;">AkarAi Customer Service <span style="font-size: 14px; font-weight: normal; color: #777;">v<?php echo AKARAI_CS_VERSION; ?></span></h2>
                    <p style="font-size: 16px; color: #555; max-width: 800px; line-height: 1.6;">
                        <?php _e( 'AkarAi is a professional AI-powered customer service solution designed to automate interactions, capture leads, and provide instant information using cutting-edge RAG (Retrieval-Augmented Generation) technology.', 'akarai-customer-service' ); ?>
                    </p>
                    <div style="margin-top: 25px; display: flex; gap: 15px;">
                        <a href="https://github.com/suleymanakar7/akarai-customer-service" target="_blank" class="button button-secondary">
                            <span class="dashicons dashicons-networks" style="margin-top:4px;"></span> GitHub Repository
                        </a>
                        <a href="https://buymeacoffee.com/sleyman777w" target="_blank" class="button" style="background: #FFDD00; color: #000; border: none; font-weight: bold; border-radius: 4px;">
                            ☕ Buy Me A Coffee
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="ai-mh-card">
                <h2><?php _e( 'Developer Information', 'akarai-customer-service' ); ?></h2>
                <table class="form-table" style="margin-top: 0;">
                    <tr>
                        <th style="width: 150px;"><?php _e( 'Company', 'akarai-customer-service' ); ?></th>
                        <td><strong>Akarca Yazılım</strong></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Address', 'akarai-customer-service' ); ?></th>
                        <td>Mersin / Turkey</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Communication', 'akarai-customer-service' ); ?></th>
                        <td><a href="mailto:info@akarcayazilim.com">info@akarcayazilim.com</a></td>
                    </tr>
                </table>
            </div>

            <div class="ai-mh-card">
                <h2><?php _e( 'Support & Contribution', 'akarai-customer-service' ); ?></h2>
                <p><?php _e( 'This plugin is open-source and free to use. If you find it helpful, please consider supporting the development or contributing on GitHub.', 'akarai-customer-service' ); ?></p>
                <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #2563eb;">
                    <p style="margin:0; font-style: italic; color: #555;">
                        "Building AI tools for the future of customer interaction."
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
