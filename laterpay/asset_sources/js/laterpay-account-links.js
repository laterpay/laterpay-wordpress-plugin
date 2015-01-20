// render LaterPay purchase dialogs using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    // render account links iframe
    if (!lpAccountLinksUrl || !lpAccountNextUrl) {
        return;
    }

    var loginLink  = 'https://web.laterpay.net/auth/user/login?_on_complete=' + lpAccountNextUrl;
    var logoutLink = 'https://web.laterpay.net/user/confirm-logout?_on_complete=' + lpAccountNextUrl;
    var signupLink = 'https://web.laterpay.net/signup/register?_on_complete=' + lpAccountNextUrl;

    var login_iframe = new Y.LaterPay.IFrame(
        Y.one('.lp_account-links'),
        lpAccountLinksUrl,
        {
            height      : '44',
            scrolling   : 'no',
            frameborder : '0',
        }
    );

    var dm = new Y.LaterPay.DialogManager();
    var accountManager = new Y.LaterPay.AccountActionHandler(dm, loginLink, logoutLink, signupLink);
    Y.on('laterpay:iFrameMessage', accountManager.onDialogXDMMessage, accountManager);

    Y.on('laterpay:dialogMessage', function(ev) {
        if (ev.msg === 'laterpay.user.logout') {
            dm.closeDialog();
            login_iframe.reload();
        }
    });
});
