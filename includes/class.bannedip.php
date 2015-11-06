<?php

class bannedIP
{
	static function getBannedType()
	{
		$userIP = getUsersIPAddress();
		$db = Database::getDatabase(true);
		$row = $db->getRow('SELECT banType FROM banned_ips WHERE ipAddress = '.$db->quote($userIP));
		if(!is_array($row))
		{
			return false;
		}
		return $row['banType'];
	}
}
?>