<?php
define("ADMIN_PUBLIC_AREA", true);
require_once('_header.inc.php');
?>

<div class="loginForm">
	<p>Login to the admin area below:</p>
	
	<?php
	if($_REQUEST['error'])
	{
		setError("Incorrect login details, please try again.");
		echo outputErrors();
	}
	?>
	
	<form method="POST" action="index.php">
		<div>
			<label for="username"><?php echo t("username"); ?></label>
			<input id="username" name="username" type="text" value="<?php echo htmlentities($_REQUEST['username']); ?>"/>
		</div>
		<div class="clear"><!-- --></div>
		
		<div>
			<label for="password"><?php echo t("password"); ?></label>
			<input id="password" name="password" type="password" value=""/>
		</div>
		<div class="clear"><!-- --></div>
		
		<div style="padding-top:10px;">
			<input id="submitme" name="submitme" value="1" type="hidden"/>
			<input id="submit" name="submit" value="login" type="submit" style="height:auto; width: 90px;"/>
		</div>
		<div class="clear"><!-- --></div>
	</form>
</div>

<div class="clear"><!-- --></div>

<?php
require_once('_footer.inc.php');
?>