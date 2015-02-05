// render LaterPay login / logout links using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    // check, if all required variables are available
    if (!lpAccountLinksUrl || !lpAccountNextUrl || !lpMerchantId) {
        return;
    }

    var dialog_api = 'https://web.laterpay.net/dialog-api';
    var self = 'https://web.laterpay.net';

    // define URLs to forward the user to after login / logout / registration
    var loginLink  = dialog_api + '?url=' + encodeURIComponent(
                                                self +
                                                '/account/dialog/login?next=' +
                                                lpAccountNextUrl +
                                                '&cp=' +
                                                lpMerchantId
                                            ),
        logoutLink = dialog_api + '?url=' + encodeURIComponent(
                                                self +
                                                '/account/dialog/logout?jsevents=1&cp=' +
                                                lpMerchantId
                                            ),
        signupLink = dialog_api + '?url=' + encodeURIComponent(
                                               self +
                                               '/account/dialog/signup?next=' +
                                               lpAccountNextUrl +
                                               '&cp=' +
                                               lpMerchantId
                                            );

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
