// render LaterPay purchase dialogs using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    var $purchaseLink   = Y.one('.lp_js_do-purchase-by-shortcode'),
        ppuContext      = {
                            showCloseBtn        : true,
                            canSkipAddToInvoice : false
                          },
        dm              = new Y.LaterPay.DialogManager();

    if (!$purchaseLink) {
        // don't register the dialogs, if there's no purchase link in the page
        return;
    }

    if ($purchaseLink.getData('preview-as-visitor')) {
        // bind event to purchase link and return, if 'preview as visitor' is activated for admins
        Y.one(Y.config.doc).delegate(
            'click',
            function(event) {
                event.preventDefault();
                alert(lpVars.i18nAlert);
            },
            '.lp_js_do-purchase-by-shortcode'
        );

        return;
    }

    dm.attachToLinks('.lp_js_do-purchase-by-shortcode', ppuContext.showCloseBtn);

});