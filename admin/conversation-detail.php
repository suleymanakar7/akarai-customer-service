<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap ai-mh-admin">
    <h1>Sohbet Detayı: <?php echo esc_html($conversation->name . ' ' . $conversation->surname); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=ai-mh-conversations'); ?>" class="button">&larr; Sohbet Listesine Dön</a>

    <div class="ai-mh-conversation-container">
        <div class="ai-mh-lead-info-card">
            <h2>Müşteri Künyesi</h2>
            <div class="ai-mh-stat">
                <span class="stat-label">Ad Soyad:</span>
                <span class="stat-value"><?php echo esc_html($conversation->name . ' ' . $conversation->surname); ?></span>
            </div>
            <div class="ai-mh-stat">
    <a href="<?php echo admin_url('admin.php?page=akarai-cs-conversations'); ?>" class="button mb-20">&larr; <?php _e( 'Back to List', 'akarai-customer-service' ); ?></a>
    
    <h1><?php printf( __( 'Conversation Detail: %s', 'akarai-customer-service' ), esc_html($conversation->name . ' ' . $conversation->surname) ); ?></h1>
    
    <div class="ai-mh-grid">
        <div class="ai-mh-main">
            <div class="ai-mh-card">
                <div class="ai-mh-chat-detail">
                    <?php if ($messages): foreach ($messages as $msg): ?>
                        <div class="chat-bubble <?php echo $msg->role === 'assistant' ? 'bubble-bot' : 'bubble-user'; ?>">
                            <div class="bubble-info">
                                <strong><?php echo $msg->role === 'assistant' ? 'AkarAi' : esc_html($conversation->name); ?></strong>
                                <small><?php echo $msg->created_at; ?></small>
                            </div>
                            <div class="bubble-content">
                                <?php echo nl2br(esc_html($msg->content)); ?>
                            </div>
                            <?php if ($msg->role === 'assistant' && isset($msg->prompt_tokens) && $msg->prompt_tokens > 0): ?>
                                <div class="bubble-tokens">
                                    <?php printf( __( 'Usage: %d prompt + %d completion tokens', 'akarai-customer-service' ), $msg->prompt_tokens, $msg->completion_tokens ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <div class="ai-mh-sidebar">
            <div class="ai-mh-card">
                <h3><?php _e( 'Lead Information', 'akarai-customer-service' ); ?></h3>
                <p><strong><?php _e( 'Full Name:', 'akarai-customer-service' ); ?></strong> <?php echo esc_html($conversation->name . ' ' . $conversation->surname); ?></p>
                <p><strong><?php _e( 'Phone:', 'akarai-customer-service' ); ?></strong> <?php echo esc_html($conversation->phone); ?></p>
                <p><strong><?php _e( 'Status:', 'akarai-customer-service' ); ?></strong> <?php echo esc_html($conversation->status); ?></p>
                <p><strong><?php _e( 'Start:', 'akarai-customer-service' ); ?></strong> <?php echo $conversation->started_at; ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.ai-mh-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; margin-top: 20px; }
.ai-mh-card { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
.ai-mh-chat-detail { background: #f0f0f1; padding: 20px; border-radius: 8px; }
.chat-bubble { margin-bottom: 20px; padding: 15px; border-radius: 12px; max-width: 80%; }
.bubble-user { margin-left: auto; background: #2563eb; color: white; border-bottom-right-radius: 2px; }
.bubble-bot { margin-right: auto; background: white; border-bottom-left-radius: 2px; border: 1px solid #ddd; }
.bubble-info { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 11px; }
.bubble-tokens { margin-top: 10px; font-size: 10px; opacity: 0.7; border-top: 1px dashed #ccc; padding-top: 5px; }
.mb-20 { margin-bottom: 20px; }
</style>
