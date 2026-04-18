<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap ai-mh-admin">
    <h1><?php _e( 'AkarAi - Content Indexing & Knowledge Base', 'akarai-customer-service' ); ?></h1>
    <p><?php _e( 'Manage the information AkarAi learns about your site from here.', 'akarai-customer-service' ); ?></p>

    <div class="ai-mh-grid">
        <div class="ai-mh-main">
            <!-- Scan Controls -->
            <div class="ai-mh-card mb-20">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2><?php _e( 'Start New Scan', 'akarai-customer-service' ); ?></h2>
                    <button id="ai-mh-open-manual-btn" class="button button-secondary"><?php _e( 'Add Manual Knowledge', 'akarai-customer-service' ); ?></button>
                </div>
                <div class="ai-mh-scan-options">
                    <div class="option-group">
                        <label><strong><?php _e( 'Content Types:', 'akarai-customer-service' ); ?></strong></label><br>
                        <label><input type="checkbox" name="post_types[]" value="post" checked> <?php _e( 'Posts', 'akarai-customer-service' ); ?></label>
                        <label><input type="checkbox" name="post_types[]" value="page" checked> <?php _e( 'Pages', 'akarai-customer-service' ); ?></label>
                        <?php 
                        $custom_types = get_post_types(['public' => true, '_builtin' => false]);
                        foreach($custom_types as $type) {
                            echo '<label><input type="checkbox" name="post_types[]" value="'.esc_attr($type).'"> '.esc_html(ucfirst($type)).'</label>';
                        }
                        ?>
                    </div>
                    <div class="option-group mt-15">
                        <label><strong><?php _e( 'Scan Mode:', 'akarai-customer-service' ); ?></strong></label><br>
                        <label><input type="radio" name="scan_type" value="incremental" checked> <?php _e( 'Incremental (New/Updated only)', 'akarai-customer-service' ); ?></label>
                        <label><input type="radio" name="scan_type" value="full"> <?php _e( 'Full Re-scan (Clear & Restart)', 'akarai-customer-service' ); ?></label>
                        <br>
                        <label><input type="checkbox" id="ai-mh-deep-scan" value="1"> <?php _e( 'Deep Scan (Full URL Render - Captures Footers/Widgets)', 'akarai-customer-service' ); ?></label>
                    </div>
                </div>
                <div id="ai-mh-scan-status-wrapper" style="display:none;" class="mt-15">
                    <div class="ai-mh-progress-info">
                        <span id="ai-mh-progress-text"><?php _e( 'Preparing...', 'akarai-customer-service' ); ?></span>
                        <span id="ai-mh-progress-percent">0%</span>
                    </div>
                    <div class="ai-mh-progress-bar">
                        <div id="ai-mh-progress-fill"></div>
                    </div>
                    <div id="ai-mh-scan-log" class="mt-10"></div>
                </div>
                <button id="ai-mh-scan-btn" class="button button-primary mt-15"><?php _e( 'Start Indexing', 'akarai-customer-service' ); ?></button>
            </div>

            <!-- Knowledge Base Table -->
            <div class="ai-mh-card">
                <h2><?php _e( 'Indexed Content (Knowledge Base)', 'akarai-customer-service' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Title', 'akarai-customer-service' ); ?></th>
                            <th><?php _e( 'Source Type', 'akarai-customer-service' ); ?></th>
                            <th><?php _e( 'Last Updated', 'akarai-customer-service' ); ?></th>
                            <th style="width: 140px;"><?php _e( 'Actions', 'akarai-customer-service' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($knowledge_base)): ?>
                            <tr><td colspan="4"><?php _e( 'No content indexed yet.', 'akarai-customer-service' ); ?></td></tr>
                        <?php else: ?>
                            <?php foreach($knowledge_base as $item): 
                                $post_type = $item->post_id == 0 ? __( 'Manual', 'akarai-customer-service' ) : get_post_type($item->post_id);
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html($item->post_title); ?></strong></td>
                                    <td><span class="badge <?php echo $item->post_id == 0 ? 'badge-manual' : ''; ?>"><?php echo esc_html($post_type); ?></span></td>
                                    <td><?php echo esc_html($item->updated_at); ?></td>
                                    <td>
                                        <button class="button-link ai-mh-view-content" data-id="<?php echo esc_attr($item->id); ?>"><?php _e( 'View', 'akarai-customer-service' ); ?></button> | 
                                        <?php if($item->post_id == 0): ?>
                                            <button class="button-link ai-mh-edit-manual" data-id="<?php echo esc_attr($item->id); ?>"><?php _e( 'Edit', 'akarai-customer-service' ); ?></button> | 
                                        <?php endif; ?>
                                        <button class="button-link-delete ai-mh-delete-index" data-id="<?php echo esc_attr($item->id); ?>"><?php _e( 'Delete', 'akarai-customer-service' ); ?></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ai-mh-sidebar">
            <div class="ai-mh-card">
                <h2><?php _e( 'Summary', 'akarai-customer-service' ); ?></h2>
                <div class="ai-mh-stat">
                    <span class="stat-label"><?php _e( 'Total Indexed:', 'akarai-customer-service' ); ?></span>
                    <span class="stat-value"><?php echo esc_html($indexed_count); ?></span>
                </div>
                <div class="ai-mh-stat">
                    <span class="stat-label"><?php _e( 'Selected Model:', 'akarai-customer-service' ); ?></span>
                    <span class="stat-value"><?php echo esc_html($settings['model'] ?? 'GPT-3.5'); ?></span>
                </div>
                <hr>
                <p class="description"><?php _e( 'Indexing determines AkarAi\'s "Knowledge Retrieval" capacity. Keep this updated as your site grows.', 'akarai-customer-service' ); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="ai-mh-manual-modal" class="ai-mh-modal" style="display:none;">
    <div class="ai-mh-modal-content">
        <span class="ai-mh-modal-close">&times;</span>
        <h2 id="manual-modal-title"><?php _e( 'Add Manual Knowledge', 'akarai-customer-service' ); ?></h2>
        <input type="hidden" id="manual-id" value="0">
        <p><label><?php _e( 'Title:', 'akarai-customer-service' ); ?></label><br><input type="text" id="manual-title" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Opening Hours', 'akarai-customer-service' ); ?>"></p>
        <p><label><?php _e( 'Detailed Content:', 'akarai-customer-service' ); ?></label><br><textarea id="manual-content" rows="10" class="widefat" placeholder="<?php esc_attr_e( 'Provide detailed information here...', 'akarai-customer-service' ); ?>"></textarea></p>
        <button id="ai-mh-save-manual-btn" class="button button-primary"><?php _e( 'Save Knowledge', 'akarai-customer-service' ); ?></button>
    </div>
</div>

<div id="ai-mh-view-modal" class="ai-mh-modal" style="display:none;">
    <div class="ai-mh-modal-content">
        <span class="ai-mh-modal-close">&times;</span>
        <h2 id="view-modal-title"><?php _e( 'View Content', 'akarai-customer-service' ); ?></h2>
        <div id="view-modal-body" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; margin-bottom: 20px;"></div>
    </div>
</div>

<style>
.mb-20 { margin-bottom: 20px; }
.mt-15 { margin-top: 15px; }
.mt-10 { margin-top: 10px; }
.ai-mh-stat { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
.stat-value { font-weight: bold; color: #2563eb; }
.badge { background: #eee; padding: 2px 6px; border-radius: 4px; font-size: 11px; text-transform: uppercase; }
.badge-manual { background: #e0f2fe; color: #0369a1; }
.ai-mh-scan-options label { margin-right: 15px; line-height: 2; }
.button-link-delete { color: #d63638; cursor: pointer; border: none; background: none; padding: 0; }
.button-link-delete:hover { color: #981b1e; }
</style>
