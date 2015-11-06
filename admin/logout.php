<?php
require_once('local_auth.inc.php');
$Auth->logout();
header("location: login.php");
exit;
?>