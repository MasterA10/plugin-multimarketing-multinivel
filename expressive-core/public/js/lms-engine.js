jQuery(document).ready(function($) {
    var lessonId = $('#lms-mark-complete').data('lesson');
    var ajaxUrl  = lms_vars.ajax_url;
    var nonce    = lms_vars.nonce;

    // 1. Initial Load and Polling for Chat
    if ($('#lms-chat-box').length) {
        fetchMessages();
        setInterval(fetchMessages, 3000); // Poll every 3 seconds
    }

    function fetchMessages() {
        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            data: {
                action: 'lms_fetch_chat_messages',
                lesson_id: lessonId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    var html = '';
                    response.data.forEach(function(msg) {
                        html += '<div class="chat-msg"><strong>' + msg.user + '</strong> <span class="time">' + msg.time + '</span><p>' + msg.message + '</p></div>';
                    });
                    $('#lms-chat-box').html(html);
                    // Scroll to bottom if new messages
                    var box = document.getElementById('lms-chat-box');
                    box.scrollTop = box.scrollHeight;
                }
            }
        });
    }

    // 2. Sending Chat Message
    $('#lms-send-chat').on('click', function() {
        var message = $('#chat-message-input').val();
        if (!message) return;

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'lms_send_chat_message',
                lesson_id: lessonId,
                message: message,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#chat-message-input').val('');
                    fetchMessages();
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // 3. Mark Lesson as Complete
    $('#lms-mark-complete').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('Processando...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'lms_mark_lesson_complete',
                lesson_id: lessonId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    btn.text('Aula Concluída!').addClass('completed');
                } else {
                    alert(response.data);
                    btn.prop('disabled', false).text('Marcar como Concluída');
                }
            }
        });
    });
});

/**
 * Global Calendar Navigation
 */
function changeLmsMonth(month, year) {
    const $container = jQuery('#lms-detailed-calendar-container');
    $container.css('opacity', '0.5').css('pointer-events', 'none');

    jQuery.post(lms_vars.ajax_url, {
        action: 'lms_get_calendar_month',
        month: month,
        year: year,
        nonce: lms_vars.nonce
    }, function(response) {
        // Find the wrapper and replace only the content 
        // to avoid losing the target if the shortcode is wrapped
        const $wrapper = $container.parent();
        $wrapper.html(response);
    });
}
