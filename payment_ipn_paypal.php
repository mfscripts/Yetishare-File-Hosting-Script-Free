<?php

require_once('includes/master.inc.php');

// check for some required variables in the request
if ((!isset($_REQUEST['payment_status'])) || (!isset($_REQUEST['business'])))
{
    die();
}

// make sure payment has completed and it's for the correct PayPal account
if (($_REQUEST['payment_status'] == "Completed") && (strtolower($_REQUEST['business']) == SITE_CONFIG_PAYPAL_PAYMENTS_EMAIL_ADDRESS))
{
    // load order using custom payment tracker hash
    $paymentTracker = $_REQUEST['custom'];
    $order = OrderPeer::loadByPaymentTracker($paymentTracker);
    if ($order)
    {
        $extendedDays = $order->days;
        $userId = $order->user_id;

        // log in payment_log
        $paypal_vars = "";
        foreach ($_REQUEST AS $k => $v)
        {
            $paypal_vars .= $k . " => " . $v . "\n";
        }
        $dbInsert = new DBObject("payment_log",
                        array("user_id", "date_created", "amount",
                            "currency_code", "from_email", "to_email", "description",
                            "request_log")
        );
        $dbInsert->user_id = $userId;
        $dbInsert->date_created = date("Y-m-d H:i:s", time());
        $dbInsert->amount = $_REQUEST['mc_gross'];
        $dbInsert->currency_code = $_REQUEST['mc_currency'];
        $dbInsert->from_email = $_REQUEST['payer_email'];
        $dbInsert->to_email = $_REQUEST['business'];
        $dbInsert->description = $extendedDays . ' days extension';
        $dbInsert->request_log = $paypal_vars;
        $dbInsert->insert();

        // make sure the amount paid matched what we expect
        if ($_REQUEST['mc_gross'] != $order->amount)
        {
            // order amounts did not match
            die();
        }

        // make sure the order is pending
        if ($order->order_status == 'completed')
        {
            // order has already been completed
            die();
        }

        // update order status to paid
        $dbUpdate = new DBObject("premium_order", array("order_status"), 'id');
        $dbUpdate->order_status = 'completed';
        $dbUpdate->id = $order->id;
        $effectedRows = $dbUpdate->update();
        if ($effectedRows === false)
        {
            // failed to update order
            die();
        }

        // extend/upgrade user
        $user = UserPeer::loadUserById($userId);
        $newExpiryDate = strtotime('+' . $order->days . ' days');
        if (($user->level == 'paid user') || ($user->level == 'admin'))
        {
            // add onto existing period
            $existingExpiryDate = strtotime($user->paidExpiryDate);

            // if less than today just revert to now
            if ($existingExpiryDate < time())
            {
                $existingExpiryDate = time();
            }

            $newExpiryDate = (int) $existingExpiryDate + (int) ($order->days * (60 * 60 * 24));
        }

        $newUserType = 'paid user';
        if ($user->level == 'admin')
        {
            $newUserType = 'admin';
        }

        // update order status to paid
        $dbUpdate = new DBObject("users", array("level", "lastPayment", "paidExpiryDate"), 'id');
        $dbUpdate->level = $newUserType;
        $dbUpdate->lastPayment = date("Y-m-d H:i:s", time());
        $dbUpdate->paidExpiryDate = date("Y-m-d H:i:s", $newExpiryDate);
        $dbUpdate->id = $userId;
        $effectedRows = $dbUpdate->update();
        if ($effectedRows === false)
        {
            // failed to update user
            die();
        }
    }
}