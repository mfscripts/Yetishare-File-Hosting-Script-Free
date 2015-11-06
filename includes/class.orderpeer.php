<?php
    class OrderPeer
    {
        // Singleton object. Leave $me alone.
        private static $me;

        static function create($user_id, $payment_hash, $days, $amount)
        {
            $dbInsert = new DBObject("premium_order",
                    array("user_id", "payment_hash", "days",
                        "amount", "order_status", "date_created"));
            $dbInsert->user_id = $user_id;
            $dbInsert->payment_hash = $payment_hash;
            $dbInsert->days = $days;
            $dbInsert->amount = $amount;
            $dbInsert->order_status = 'pending';
            $dbInsert->date_created = date("Y-m-d H:i:s", time());
            if($dbInsert->insert())
            {
                return $dbInsert;
            }

            return false;
        }
        
        static function loadByPaymentTracker($paymentHash)
        {
            $orderObj = new Order();
            $orderObj->select($paymentHash, 'payment_hash');
            if(!$orderObj->ok())
            {
                return false;
            }

            return $orderObj;
        }
    }
