<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('username', 'password', 'level', 'email', 'paidExpiryDate', 'firstname', 'lastname', 'title'), $id);
        }
    }
    
    class Order extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('premium_order', array('user_id', 'payment_hash', 'days', 'amount', 'order_status'), $id);
        }
    }

