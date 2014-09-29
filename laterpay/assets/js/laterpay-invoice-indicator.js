// render LaterPay purchase dialogs using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

	// render invoice indicator iframe
	if (!lpInvoiceIndicatorVars || !lpInvoiceIndicatorVars.lpBalanceUrl) {
		// don't render the invoice indicator, if no URL is provided in the variables
		return;
	}

	new Y.LaterPay.IFrame(
		Y.one('#laterpay-invoice-indicator'),
		lpInvoiceIndicatorVars.lpBalanceUrl,
		{
			width       : '110',
			height      : '30',
			scrolling   : 'no',
			frameborder : '0'
		}
	);

});
