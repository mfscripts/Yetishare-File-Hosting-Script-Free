<?php
require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* get vars */
$id = $_REQUEST['id'];

$response = array();
$response['content'] 	= "";
$response['javascript']	= "";
$response['errors']		= array();
$response['success']	= 0;

/* get user details */
$sql = "SELECT status, level, title, firstname, lastname, email, paidExpiryDate FROM users WHERE id = ".(int)$id;
$row = $db->getRow($sql);
if($row)
{
	$response['status'] = $row['status'];
	$response['level'] = $row['level'];
	$response['title'] = $row['title'];
	$response['firstname'] = $row['firstname'];
	$response['lastname'] = $row['lastname'];
	$response['email'] = $row['email'];
	$response['id'] = $id;
	$response['success'] = 1;
        $response['paidExpiryDate'] = $row['paidExpiryDate'];
}
else
{
	$response['errors'] = array('Could not load user details.');
}

echo json_encode($response);

?>