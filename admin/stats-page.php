<?php if (!defined('ABSPATH')) exit; ?>
    <div class="ai-mh-stats-grid">
        <div class="ai-mh-stat-card ai-mh-stat-blue">
            <div class="ai-mh-stat-icon">💬</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_convs); ?></div>
            <div class="ai-mh-stat-label"><?php _e( 'Total Conversations', 'akarai-customer-service' ); ?></div>
            <div class="ai-mh-stat-today"><?php _e( 'Today:', 'akarai-customer-service' ); ?> <strong><?php echo $today_convs; ?></strong></div>
        </div>

        <div class="ai-mh-stat-card ai-mh-stat-green">
            <div class="ai-mh-stat-icon">👤</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_leads); ?></div>
            <div class="ai-mh-stat-label"><?php _e( 'Total Leads', 'akarai-customer-service' ); ?></div>
            <div class="ai-mh-stat-today"><?php _e( 'Today:', 'akarai-customer-service' ); ?> <strong><?php echo $today_leads; ?></strong></div>
        </div>

        <div class="ai-mh-stat-card ai-mh-stat-purple">
            <div class="ai-mh-stat-icon">📨</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_msgs); ?></div>
            <div class="ai-mh-stat-label"><?php _e( 'User Messages', 'akarai-customer-service' ); ?></div>
            <div class="ai-mh-stat-today"><?php _e( 'Total sent', 'akarai-customer-service' ); ?></div>
        </div>

        <div class="ai-mh-stat-card ai-mh-stat-orange">
            <div class="ai-mh-stat-icon">⚡</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_tokens); ?></div>
            <div class="ai-mh-stat-label"><?php _e( 'Token Usage', 'akarai-customer-service' ); ?></div>
            <div class="ai-mh-stat-today">≈ $<?php echo $est_cost; ?> (<?php echo esc_html($model); ?>)</div>
        </div>
    </div>

    <!-- TOKEN BREAKDOWN -->
    <div class="ai-mh-stats-row">
        <div class="ai-mh-card ai-mh-stat-detail-card">
            <h2>🔋 <?php _e( 'Token Details', 'akarai-customer-service' ); ?></h2>
            <table class="ai-mh-stats-table">
                <tr>
                    <td>🟦 <?php _e( 'Prompt Tokens', 'akarai-customer-service' ); ?></td>
                    <td><strong><?php echo number_format($total_prompt); ?></strong></td>
                </tr>
                <tr>
                    <td>🟩 <?php _e( 'Completion Tokens', 'akarai-customer-service' ); ?></td>
                    <td><strong><?php echo number_format($total_comp); ?></strong></td>
                </tr>
                <tr>
                    <td>⚪ <?php _e( 'Total', 'akarai-customer-service' ); ?></td>
                    <td><strong><?php echo number_format($total_tokens); ?></strong></td>
                </tr>
                <tr>
                    <td>💵 <?php _e( 'Estimated Cost', 'akarai-customer-service' ); ?></td>
                    <td><strong>$<?php echo $est_cost; ?></strong></td>
                </tr>
                <tr>
                    <td>🤖 <?php _e( 'Model Used', 'akarai-customer-service' ); ?></td>
                    <td><strong><?php echo esc_html($model); ?></strong></td>
                </tr>
            </table>

            <?php if ($total_tokens > 0): ?>
            <div class="ai-mh-token-bar-wrap">
                <div class="ai-mh-token-bar-label"><?php _e( 'Prompt / Completion Ratio', 'akarai-customer-service' ); ?></div>
 drum                <div class="ai-mh-token-bar">
                    <?php
                        $prompt_pct = $total_tokens > 0 ? round(($total_prompt / $total_tokens) * 100) : 0;
                        $comp_pct   = 100 - $prompt_pct;
                    ?>
                    <div class="ai-mh-token-bar-prompt" style="width:<?php echo $prompt_pct; ?>%">
                        <?php echo $prompt_pct; ?>%
                    </div>
                    <div class="ai-mh-token-bar-comp" style="width:<?php echo $comp_pct; ?>%">
                        <?php echo $comp_pct; ?>%
                    </div>
                </div>
                <div class="ai-mh-bar-legend">
                    <span class="ai-mh-legend-dot ai-mh-legend-prompt"></span> <?php _e( 'Prompt', 'akarai-customer-service' ); ?> &nbsp;
                    <span class="ai-mh-legend-dot ai-mh-legend-comp"></span> <?php _e( 'Completion', 'akarai-customer-service' ); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SON 7 GÜN -->
        <div class="ai-mh-card ai-mh-stat-detail-card">
            <h2>📅 <?php _e( 'Last 7 Days', 'akarai-customer-service' ); ?></h2>
            <table class="ai-mh-stats-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Date', 'akarai-customer-service' ); ?></th>
                        <th><?php _e( 'Lead', 'akarai-customer-service' ); ?></th>
                        <th><?php _e( 'Conversation', 'akarai-customer-service' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    // Build a merged daily map
                    $daily = [];
                    for ($i = 6; $i >= 0; $i--) {
                        $d = date('Y-m-d', strtotime("-$i days"));
                        $daily[$d] = ['lead' => 0, 'conv' => 0];
                    }
                    foreach ($leads_trend as $row) $daily[$row->date]['lead'] = $row->count;
                    foreach ($convs_trend as $row) $daily[$row->date]['conv'] = $row->count;

                    foreach ($daily as $date => $vals): ?>
                    <tr>
                        <td><?php echo date_i18n('d M', strtotime($date)); ?></td>
                        <td><span class="ai-mh-badge ai-mh-badge-green"><?php echo $vals['lead']; ?></span></td>
                        <td><span class="ai-mh-badge ai-mh-badge-blue"><?php echo $vals['conv']; ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="ai-mh-stats-note">
        ⚠️ <?php _e( 'Token and cost data are only recorded after updating to the latest version. Past chats will appear as 0.', 'akarai-customer-service' ); ?>
    </p>
</div>
