<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap ai-mh-admin">
            <div class="ai-mh-stat-label">Toplam Konuşma</div>
            <div class="ai-mh-stat-today">Bugün: <strong><?php echo $today_convs; ?></strong></div>
        </div>

        <div class="ai-mh-stat-card ai-mh-stat-green">
            <div class="ai-mh-stat-icon">👤</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_leads); ?></div>
            <div class="ai-mh-stat-label">Toplam Lead</div>
            <div class="ai-mh-stat-today">Bugün: <strong><?php echo $today_leads; ?></strong></div>
        </div>

        <div class="ai-mh-stat-card ai-mh-stat-purple">
            <div class="ai-mh-stat-icon">📨</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_msgs); ?></div>
            <div class="ai-mh-stat-label">Kullanıcı Mesajı</div>
            <div class="ai-mh-stat-today">Toplam gönderilen</div>
        </div>

        <div class="ai-mh-stat-card ai-mh-stat-orange">
            <div class="ai-mh-stat-icon">⚡</div>
            <div class="ai-mh-stat-value"><?php echo number_format($total_tokens); ?></div>
            <div class="ai-mh-stat-label">Token Kullanımı</div>
            <div class="ai-mh-stat-today">≈ $<?php echo $est_cost; ?> (<?php echo esc_html($model); ?>)</div>
        </div>
    </div>

    <!-- TOKEN BREAKDOWN -->
    <div class="ai-mh-stats-row">
        <div class="ai-mh-card ai-mh-stat-detail-card">
            <h2>🔋 Token Detayı</h2>
            <table class="ai-mh-stats-table">
                <tr>
                    <td>🟦 Prompt Tokenları</td>
                    <td><strong><?php echo number_format($total_prompt); ?></strong></td>
                </tr>
                <tr>
                    <td>🟩 Completion Tokenları</td>
                    <td><strong><?php echo number_format($total_comp); ?></strong></td>
                </tr>
                <tr>
                    <td>⚪ Toplam</td>
                    <td><strong><?php echo number_format($total_tokens); ?></strong></td>
                </tr>
                <tr>
                    <td>💵 Tahmini Maliyet</td>
                    <td><strong>$<?php echo $est_cost; ?></strong></td>
                </tr>
                <tr>
                    <td>🤖 Kullanılan Model</td>
                    <td><strong><?php echo esc_html($model); ?></strong></td>
                </tr>
            </table>

            <?php if ($total_tokens > 0): ?>
            <div class="ai-mh-token-bar-wrap">
                <div class="ai-mh-token-bar-label">Prompt / Completion Oranı</div>
                <div class="ai-mh-token-bar">
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
                    <span class="ai-mh-legend-dot ai-mh-legend-prompt"></span> Prompt &nbsp;
                    <span class="ai-mh-legend-dot ai-mh-legend-comp"></span> Completion
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SON 7 GÜN -->
        <div class="ai-mh-card ai-mh-stat-detail-card">
            <h2>📅 Son 7 Gün</h2>
            <table class="ai-mh-stats-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Lead</th>
                        <th>Konuşma</th>
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
                        <td><?php echo date('d M', strtotime($date)); ?></td>
                        <td><span class="ai-mh-badge ai-mh-badge-green"><?php echo $vals['lead']; ?></span></td>
                        <td><span class="ai-mh-badge ai-mh-badge-blue"><?php echo $vals['conv']; ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="ai-mh-stats-note">
        ⚠️ Token ve maliyet verileri yalnızca bu ekletiyi güncellediğiniz tarihten itibaren kaydedilmektedir. Geçmiş sohbetler 0 olarak görünür.
    </p>
</div>
