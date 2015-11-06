<?php
    class UserPeer
    {
        // Singleton object. Leave $me alone.
        private static $me;

        static function create($username, $password, $email, $title, $firstname, $lastname, $accType = 'user')
        {
            $dbInsert = new DBObject("users",
                    array("username", "password", "email",
                        "title", "firstname", "lastname", "datecreated",
                        "createdip", "status", "level", "paymentTracker")
                    );
            $dbInsert->username 		= $username;
            $dbInsert->password 		= MD5($password);
            $dbInsert->email                    = $email;
            $dbInsert->title 			= $title;
            $dbInsert->firstname		= $firstname;
            $dbInsert->lastname			= $lastname;
            $dbInsert->datecreated		= sqlDateTime();
            $dbInsert->createdip		= getUsersIPAddress();
            $dbInsert->status			= 'active';
            $dbInsert->level                    = 'free user';
            $dbInsert->paymentTracker           = MD5(time().$username);
            if($dbInsert->insert())
            {
                return $dbInsert;
            }

            return false;
        }
		
		static function createPasswordResetHash($userId)
		{
			$user = true;
			
			// make sure it doesn't already exist on an account
			while($user != false)
			{
				// create hash
				$hash = MD5(microtime().$userId);
				
				// lookup by hash
				$user = self::loadUserByPasswordResetHash($hash);
			}
			
			// update user with hash
			$db = Database::getDatabase(true);
			$db->query('UPDATE users SET passwordResetHash = :passwordResetHash WHERE id = :id', array('passwordResetHash' => $hash, 'id'     => $userId));
			
			return $hash;
		}

        static function loadUserById($id)
        {
            $userObj = new User();
            $userObj->select($id, 'id');
            if(!$userObj->ok())
            {
                return false;
            }

            return $userObj;
        }
        
        static function loadUserByUsername($username)
        {
            $userObj = new User();
            $userObj->select($username, 'username');
            if(!$userObj->ok())
            {
                return false;
            }

            return $userObj;
        }
        
        static function loadUserByPaymentTracker($paymentTracker)
        {
            $userObj = new User();
            $userObj->select($paymentTracker, 'paymentTracker');
            if(!$userObj->ok())
            {
                return false;
            }

            return $userObj;
        }

        static function loadUserByEmailAddress($email)
        {
            $userObj = new User();
            $userObj->select($email, 'email');
            if(!$userObj->ok())
            {
                return false;
            }

            return $userObj;
        }
		
		static function loadUserByPasswordResetHash($hash)
        {
            $userObj = new User();
            $userObj->select($hash, 'passwordResetHash');
            if(!$userObj->ok())
            {
                return false;
            }

            return $userObj;
        }
    }
