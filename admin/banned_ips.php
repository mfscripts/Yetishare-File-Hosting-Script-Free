<?php
require_once('_header.inc.php');

/* do any deletions */
if($r = (int)$_REQUEST['r'])
{
	$db->query("DELETE FROM banned_ips WHERE id = ".$r." LIMIT 1");
}

?>
	
<p><?php echo t("banned_ips_intro"); ?></p>

<div id="cellediting" class="yuiTable"></div>

<ul class="navLink" style="padding-top: 10px;">
	<li><a href="#" onClick="displayBannedIpPopup(); return false;"><?php echo t("banned_ips_add_banned_ip"); ?></a></li>
</ul>

<?php
/* load in config options from db */
$rows = $db->getRows("SELECT * FROM banned_ips ORDER BY ipAddress");
if(COUNT($rows) > 0)
{
?>
	<script>
	mfScripts.bannedIPsTable = {};

	mfScripts.bannedIPsTable.Data = {
		configData: [
			<?php
				$formattedRow = array();
				foreach($rows AS $row)
				{
					$banNotes = $row['banNotes']?$row['banNotes']:"-";
					$banNotes = str_replace(array("\n", "\r"), "<br/>", $banNotes);
					$formattedRow[] = "{id:\"".$row['id']."\", ip_address:\"".addslashes($row['ipAddress'])."\", date_banned:\"".dater($row['dateBanned'])."\", ban_type:\"".addslashes($row['banType'])."\", ban_notes:\"".addslashes($banNotes)."\", ban_action:\"<a href='#' onClick='return removeBannedIP(".$row['id'].");'>".t("remove")."</a>\"}";
				}
				echo implode(",", $formattedRow);
			?>
		]
	};

	YAHOO.util.Event.addListener(window, "load", function()
	{
		mfScripts.bannedIPsTable.InlineCellEditing = function()
		{
			var myColumnDefs = [
				{key:"id", hidden:true},
				{key:"ip_address", label:"IP Address", sortable:true},
				{key:"date_banned", label:"Date Banned", sortable:true},
				{key:"ban_type", label:"Ban Type", sortable:true},
				{key:"ban_notes", label:"Ban Notes", sortable:true},
				{key:"ban_action", label:"Action", sortable:false}
			];

			var myDataSource = new YAHOO.util.DataSource(mfScripts.bannedIPsTable.Data.configData);
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
			myDataSource.responseSchema = {
				fields: ["id","ip_address","date_banned","ban_type","ban_notes","ban_action"]
			};

			var myDataTable = new YAHOO.widget.DataTable("cellediting", myColumnDefs, myDataSource, {selectionMode:"single"});

			myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
			myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
			myDataTable.subscribe("rowClickEvent", myDataTable.onEventSelectRow);
			
			return {
				oDS: myDataSource,
				oDT: myDataTable
			};
		}();
		
	});
	</script>
<?php
}
?>
	<script>
	/* popup widget */
	YAHOO.util.Event.addListener(window, "load", function(){
		mfScripts.newBannedIPPopup = new YAHOO.widget.Dialog("newBannedIPPopup", 
			{
				width : "400px",
				modal : true,
				zindex : 4,
				fixedcenter : true,
				visible : false, 
				constraintoviewport : true
			});

		mfScripts.newBannedIPPopup.render(document.body);

		new YAHOO.inputEx.Form({
			parentEl: 'myFormContainer',
			fields: [
					{type:'group',inputParams:
							{legend:'IP Details', name:'group1', className:'inputEx-Group-Custom', fields:
								[
									{inputParams: {label: t('ip_address'), name: 'ip_address', required: true } }, 
									{type: 'select', inputParams: {label: t('ban_from'), name: 'ban_type', selectValues: ['Uploading', 'Whole Site'] } },
									{type: 'text', inputParams: {label: t('notes'), name: 'notes' } }
								]
							}
					}
					],
			buttons: [{type: 'submit', value: t('add_banned_ip')}],
			ajax: {
				method: 'POST',
				uri: 'ajax/addNewBannedIP.ajax.php',
				callback: {
					success: function(o)
					{
						var responseObj = YAHOO.lang.JSON.parse(o.responseText);
						if(responseObj.success == 0)
						{
							/* error */
							var errorStr = "Error:\n";
							for(e in responseObj.errors)
							{
								errorStr += responseObj.errors[e]+"\n";
							}
							alert(errorStr);
						}
						else
						{
							/* success */
							window.location = "banned_ips.php";
						}
					},
					failure: function(o)
					{
						alert(t("error_submitting_form"));
					}
				},
				showMask: true
			}
		});
	});
		 
	function displayBannedIpPopup()
	{
		mfScripts.newBannedIPPopup.show();
	}
	</script>

<?php
require_once('_footer.inc.php');
?>

<div id="newBannedIPPopup" class="yui-pe-content">
	<div class="hd"><?php echo t("enter_ip_address_details"); ?></div>
	<div class="bd">
		<div class='demoContainer' id='myFormContainer'></div>
	</div>
</div>