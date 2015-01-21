// render LaterPay login / logout links using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    // check, if all required variables are available
    if (!lpAccountLinksUrl || !lpAccountNextUrl || !lpMerchantId) {
        return;
    }

    var self = 'https://web.laterpay.net';

    // define URLs to forward the user to after login / logout / registration
    var loginLink  = self + '/account/dialog/login?next=' + lpAccountNextUrl + '&cp=' + lpMerchantId,
        logoutLink = self + '/account/dialog/logout?next=' + lpAccountNextUrl + '&cp=' + lpMerchantId,
        signupLink = self + '/account/dialog/signup?next=' + lpAccountNextUrl + '&cp=' + lpMerchantId;

    // render iframe inside of placeholder
    var login_iframe = new Y.LaterPay.IFrame(
        Y.one('.lp_account-links'),
        lpAccountLinksUrl,
        {
            height      : '42',
            width       : '210',
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
