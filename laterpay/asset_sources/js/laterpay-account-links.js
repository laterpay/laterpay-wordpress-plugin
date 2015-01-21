// render LaterPay login / logout links using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    // check, if all required variables are available
    if (!lpAccountLinksUrl || !lpAccountNextUrl) {
        return;
    }

    // define URLs to forward the user to after login / logout / registration
    var loginLink  = 'https://web.laterpay.net/auth/user/login?_on_complete=' + lpAccountNextUrl,
        logoutLink = 'https://web.laterpay.net/user/confirm-logout?_on_complete=' + lpAccountNextUrl,
        signupLink = 'https://web.laterpay.net/signup/register?_on_complete=' + lpAccountNextUrl;

    // render iframe inside of placeholder
    var login_iframe = new Y.LaterPay.IFrame(
        Y.one('.lp_account-links'),
        lpAccountLinksUrl,
        {
            height      : '44',
            scrolling   : 'no',
            frameborder : '0',
        }
    );

    var dm              = new Y.LaterPay.DialogManager(),
        accountManager  = new Y.LaterPay.AccountActionHandler(dm, loginLink, logoutLink, signupLink);

    Y.on('laterpay:iFrameMessage', accountManager.onDialogXDMMessage, accountManager);

    Y.on('laterpay:dialogMessage', function(ev) {
        if (ev.msg === 'laterpay.user.logout') {
            dm.closeDialog();
            login_iframe.reload();
        }
    });
});
