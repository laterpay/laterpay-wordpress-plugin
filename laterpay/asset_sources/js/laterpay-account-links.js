// render LaterPay login / logout links using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    // render iframe inside of placeholder
    var loginIframe   = new Y.LaterPay.IFrame(
                            Y.one('.lp_account-links'),
                            lpVars.iframeLink,
                            {
                                height      : '42',
                                width       : '210',
                                scrolling   : 'no',
                                frameborder : '0'
                            }
                        ),
       dm             = new Y.LaterPay.DialogManager(),
       accountManager = new Y.LaterPay.AccountActionHandler(dm, lpVars.loginLink, lpVars.logoutLink, lpVars.signupLink);

    Y.on('laterpay:iFrameMessage', accountManager.onDialogXDMMessage, accountManager);

    Y.on('laterpay:dialogMessage', function(ev) {
        if (ev.msg === 'laterpay.user.logout') {
            dm.closeDialog();
            loginIframe.reload();
        }
    });
});
