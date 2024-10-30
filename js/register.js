// Need to copy user_email to user_login for registration.
$('#registerform').bind('submit', (e) => {
    $('#user_login').val($('#user_email').val());
    return true;
});
