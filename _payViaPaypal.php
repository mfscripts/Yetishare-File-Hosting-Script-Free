<?php

require_once('includes/master.inc.php');

if(!isset($_REQUEST['days']))
{
	redirect(WEB_ROOT . '/index.html');
}

/* require login */
$Auth->requireUser('login.php');

$days = (int)(trim($_REQUEST['days']));

// create order entry
$orderHash = MD5(time().$Auth->id);
$amount = number_format(constant('SITE_CONFIG_COST_FOR_'.$days.'_DAYS_PREMIUM'), 2);
$order = OrderPeer::create($Auth->id, $orderHash, $days, $amount);
if($order)
{
	// redirect to PayPal
	$desc = $days.' days extension for '.$Auth->username;
	$paypalUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&notify_url='.urlencode(WEB_ROOT.'/payment_ipn_paypal.php').'&email='.urlencode($Auth->email).'&return='.urlencode(WEB_ROOT.'/payment_complete.'.SITE_CONFIG_PAGE_EXTENSION).'&business='.urlencode(SITE_CONFIG_PAYPAL_PAYMENTS_EMAIL_ADDRESS).'&item_name='.urlencode($desc).'&item_number=1&amount='.urlencode($amount).'&no_shipping=2&no_note=1&currency_code='.SITE_CONFIG_COST_CURRENCY_CODE.'&lc=GB&bn=PP%2dBuyNowBF&charset=UTF%2d8&custom='.$orderHash;
	redirect($paypalUrl);
}
