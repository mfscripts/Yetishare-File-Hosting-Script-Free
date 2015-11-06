<?php
require_once('_header.inc.php');

if(!isset($_REQUEST['s']))
{
    $serverId = 1;
}
else
{
    $serverId = (int)$_REQUEST['s'];
}

?>
<p><?php echo t("file_server_test_ftp_intro", "Testing connection to file server... (via ftp)"); ?></p>
<?php

/* load server details */
$sQL = "SELECT file_server.* ";
$sQL .= "FROM file_server ";
$sQL .= "WHERE file_server.serverType = 'remote' AND id=".(int)$serverId;
$row = $db->getRow($sQL);
if(!$row)
{
    echo t("could_not_load_server", "Could not load server details.");
}
else
{
    $error = '';

    // start output buffering
    ob_start();
    ob_end_flush();
	
	echo '<p>- Making sure ftp functions are available in PHP... ';
	
	// make sure ftp functions exists
	if (!function_exists('ftp_connect'))
	{
		$error = 'Could not find PHP ftp functions! Please contact your host to request they\'re enabled.';
	}
	
	// output results
    ob_start();
    ob_end_flush();
	
	if(strlen($error) == 0)
    {
		echo '<font style="color: green;">FTP functions found.</font></p>';
		echo '<p>- Finding file server '.$row['serverLabel'].' on ip '.$row['ipAddress'].' (port: '.$row['ftpPort'].')... ';
		
		// connect via ftp
		$conn_id = ftp_connect($row['ipAddress'], $row['ftpPort'], 30);
		if($conn_id === false)
		{
			$error = 'Could not connect!';
		}
	}
    
    // output results
    ob_start();
    ob_end_flush();

    if(strlen($error) == 0)
    {
        echo '<font style="color: green;">Successfully found.</font></p>';
        echo '<p>- Authenticating with stored user \''.$row['ftpUsername'].'\' and password [HIDDEN]... ';
    
        // authenticate
        $login_result = ftp_login($conn_id, $row['ftpUsername'], $row['ftpPassword']);
        if($login_result === false)
        {
            $error = 'Could not authenticate!';
            // close ftp
            ftp_close($conn_id);
        }
    }
    
    // output results
    ob_start();
    ob_end_flush();
    
    if(strlen($error) == 0)
    {
        echo '<font style="color: green;">Successfully authenticated.</font></p>';
        echo '<p>- Changing to storage directory: '.$row['storagePath'].'... ';
        
        // change directory
        if (ftp_chdir($conn_id, $row['storagePath']) === false)
        {
            $error = 'Could not find storage directory!';
            // close ftp
            ftp_close($conn_id);
        }
    }
    
    // output results
    ob_start();
    ob_end_flush();
    
    if(strlen($error) == 0)
    {
        echo '<font style="color: green;">Successfully changed directory.</font></p>';
        // close ftp
        ftp_close($conn_id);
        echo '<p>- Disconnected from ftp.</p>';
    }
    
    // output results
    ob_start();
    ob_end_flush();
    
    if(strlen($error) > 0)
    {
        echo '<font style="color: red; font-weight:bold;">'.$error.'</font></p>';
    }
    else
    {
        echo '<p style="color: green; font-weight:bold;">- No errors found connecting to '.$row['serverLabel'].'.</p>';
    }
}
?>

<ul class="navLink" style="padding-top: 10px;">
    <li><a href="file_server_management.php"><?php echo t("back_to_server_management", "Back to Server Management"); ?></a></li>
</ul>

<?php
require_once('_footer.inc.php');
?>