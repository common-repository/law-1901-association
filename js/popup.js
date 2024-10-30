// Call ajax when popup close button is hit.
$('#wpbody-content').bind('click', (e) => {
    if (($(e.target).prop('nodeName') === 'BUTTON') &&
        ($(e.target).parent().attr('id') === 'law-1901-association-notice')
    ) {
        e.stopImmediatePropagation();
        $.post(
            ajaxurl,
            {
                'action': 'dismiss_law1901_notice',
                '_wpnonce': '<?php echo esc_attr( wp_create_nonce( 'ajax-nonce' ) ); ?>',
                'notification': $(e.target).parent().data('notification')
            }
        );
    }
});
