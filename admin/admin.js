jQuery(document).ready(function($) {
    // Advanced Scan Site
    // Batch Indexing System
    $('#ai-mh-scan-btn').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $wrapper = $('#ai-mh-scan-status-wrapper');
        const $text = $('#ai-mh-progress-text');
        const $percent = $('#ai-mh-progress-percent');
        const $fill = $('#ai-mh-progress-fill');
        const $log = $('#ai-mh-scan-log');

        const postTypes = [];
        $('input[name="post_types[]"]:checked').each(function() {
            postTypes.push($(this).val());
        });

        const isFullScan = $('input[name="scan_type"]:checked').val() === 'full';

        if (postTypes.length === 0) {
            alert('Please select at least one content type.');
            return;
        }

        $btn.prop('disabled', true).text('Preparing...');
        $wrapper.fadeIn();
        $log.empty();
        $fill.css('width', '0%');
        $percent.text('0%');

        // Step 1: Prepare (Get Post IDs)
        $.post(akarai_cs_admin.ajax_url, {
            action: 'ai_mh_prepare_indexing',
            nonce: akarai_cs_admin.nonce,
            post_types: postTypes,
            full_scan: isFullScan
        }, function(response) {
            if (response.success) {
                const ids = response.data.ids;
                const total = response.data.total;
                
                if (total === 0) {
                    $text.text('No new content found to scan.');
                    $btn.prop('disabled', false).text('Start Indexing');
                    return;
                }

                $text.text('Indexing started (0 / ' + total + ')');
                indexBatch(ids, 0, total);
            } else {
                alert('An error occurred during preparation.');
                $btn.prop('disabled', false).text('Start Indexing');
            }
        });

        function indexBatch(ids, currentIndex, total) {
            if (currentIndex >= total) {
                $text.text('Indexing Complete! ( ' + total + ' / ' + total + ' )');
                $log.append('<div class="log-item success">✓ All items processed successfully.</div>');
                $btn.prop('disabled', false).text('Start Indexing');
                setTimeout(() => location.reload(), 2000);
                return;
            }

            const currentId = ids[currentIndex];
            $text.text('Scanning: ' + (currentIndex + 1) + ' / ' + total);

            $.post(akarai_cs_admin.ajax_url, {
                action: 'ai_mh_index_batch',
                nonce: akarai_cs_admin.nonce,
                post_id: currentId
            }, function(response) {
                const p = Math.round(((currentIndex + 1) / total) * 100);
                $fill.css('width', p + '%');
                $percent.text(p + '%');

                if (response.success) {
                    $log.append('<div class="log-item success">' + (currentIndex + 1) + '. ' + response.data.message + '</div>');
                } else {
                    $log.append('<div class="log-item error">' + (currentIndex + 1) + '. Error: ' + response.data + '</div>');
                }

                // Scroll log to bottom
                $log.scrollTop($log[0].scrollHeight);

                // Process Next
                indexBatch(ids, currentIndex + 1, total);
            }).fail(function() {
                $log.append('<div class="log-item error">Server did not respond, skipping item...</div>');
                indexBatch(ids, currentIndex + 1, total);
            });
        }
    });

    // Delete Individual Index
    $('.ai-mh-delete-index').on('click', function() {
        if (!confirm('Are you sure you want to delete this content from the index?')) return;
        
        const $btn = $(this);
        const id = $btn.data('id');

        $.post(akarai_cs_admin.ajax_url, {
            action: 'ai_mh_delete_index',
            nonce: akarai_cs_admin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                $btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
            }
        });
    });

    // Modal Control
    const $manualModal = $('#ai-mh-manual-modal');
    const $viewModal = $('#ai-mh-view-modal');

    $('#ai-mh-open-manual-btn').on('click', function() {
        $('#manual-modal-title').text('Add Manual Knowledge');
        $('#manual-id').val(0);
        $('#manual-title').val('');
        $('#manual-content').val('');
        $manualModal.fadeIn();
    });

    $('.ai-mh-modal-close').on('click', function() {
        $('.ai-mh-modal').fadeOut();
    });

    // Save Manual
    $('#ai-mh-save-manual-btn').on('click', function() {
        const id = $('#manual-id').val();
        const title = $('#manual-title').val();
        const content = $('#manual-content').val();

        if (!title || !content) return alert('Please fill in all fields.');

        $(this).prop('disabled', true).text('Saving...');

        $.post(akarai_cs_admin.ajax_url, {
            action: 'ai_mh_save_manual',
            nonce: akarai_cs_admin.nonce,
            id: id,
            title: title,
            content: content
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('An error occurred while saving.');
                $('#ai-mh-save-manual-btn').prop('disabled', false).text('Save');
            }
        });
    });

    // View Content
    $('.ai-mh-view-content').on('click', function() {
        const id = $(this).data('id');
        $('#view-modal-body').text('Loading...');
        $viewModal.fadeIn();

        $.post(akarai_cs_admin.ajax_url, {
            action: 'ai_mh_get_index_content',
            nonce: akarai_cs_admin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                $('#view-modal-title').text(response.data.post_title);
                $('#view-modal-body').text(response.data.content);
            } else {
                $('#view-modal-body').text('Content could not be loaded.');
            }
        });
    });

    // Edit Manual
    $('.ai-mh-edit-manual').on('click', function() {
        const id = $(this).data('id');
        
        $.post(akarai_cs_admin.ajax_url, {
            action: 'ai_mh_get_index_content',
            nonce: akarai_cs_admin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                $('#manual-modal-title').text('Edit Knowledge');
                $('#manual-id').val(response.data.id);
                $('#manual-title').val(response.data.post_title);
                $('#manual-content').val(response.data.content);
                $manualModal.fadeIn();
            }
        });
    });

    // Media Uploader
    $('.ai-mh-upload-btn').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({
            title: 'Select Header Image',
            button: { text: 'Use this Image' },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#ai-mh-header-image-id').val(attachment.id);
            $('.ai-mh-image-preview').html('<img src="' + attachment.url + '" style="max-width:150px;">');
            $('.ai-mh-remove-btn').show();
        });

        frame.open();
    });

    $('.ai-mh-remove-btn').on('click', function() {
        $('#ai-mh-header-image-id').val('');
        $('.ai-mh-image-preview').empty();
        $(this).hide();
    });
});
