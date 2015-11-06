<?php
require_once('_header.inc.php');
?>
	
<p><?php echo t("settings_intro"); ?></p>

<div id="cellediting" class="yuiTable"></div>

<?php
/* load in config options from db */
$rows = $db->getRows("SELECT * FROM site_config ORDER BY config_group, config_description, config_key");
if(COUNT($rows) > 0)
{
?>
	<script>
	mfScripts.settingsTable = {};

	mfScripts.settingsTable.Data = {
		configData: [
			<?php
				$formattedRow = array();
				foreach($rows AS $row)
				{
					/* check for any sql and load it in if required */
					$availableValues = $row['availableValues'];
					if(substr($availableValues, 0, 6) == "SELECT")
					{
						$valueRows = $db->getRows($availableValues);
						if(COUNT($valueRows) > 0)
						{
							$rs = array();
							foreach($valueRows AS $valueRow)
							{
								$rs[] = "\"".addslashes($valueRow['itemValue'])."\"";
							}
						}
						$availableValues = "[".implode(",", $rs)."]";
					}

					$formattedRow[] = "{id:\"".$row['id']."\", key:\"".$row['config_description']."\", value:\"".addslashes(str_replace("</script", "<\/script", str_replace(array("\n", "\r"), " ", ($row['config_value']))))."\", editor_type:\"".addslashes($row['config_type'])."\", group:\"".addslashes($row['config_group'])."\", available_values:".(($availableValues)?$availableValues:"\"\"")."}";
				}
				echo implode(",", $formattedRow);
			?>
		]
	};

	YAHOO.util.Event.addListener(window, "load", function()
	{
		mfScripts.settingsTable.InlineCellEditing = function()
		{
			var myColumnDefs = [
				{key:"id", hidden:true},
				{key:"editor_type", hidden:true},
				{key:"available_values", hidden:true},
				{key:"group", label:t("group"), sortable:true},
				{key:"key", label:t("config_description"), sortable:true},
				{key:"value", label:t("config_value"), editor: new YAHOO.widget.TextareaCellEditor(), sortable:true}
			];

			var myDataSource = new YAHOO.util.DataSource(mfScripts.settingsTable.Data.configData);
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
			myDataSource.responseSchema = {
				fields: ["id","key","value","editor_type","available_values","group"]
			};

			var myDataTable = new YAHOO.widget.DataTable("cellediting", myColumnDefs, myDataSource, {});

			// Set up editing flow
			var highlightEditableCell = function(oArgs) {
				var elCell = oArgs.target;
				if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {
					this.highlightCell(elCell);
				}
			};
			myDataTable.subscribe("cellMouseoverEvent", highlightEditableCell);
			myDataTable.subscribe("cellMouseoutEvent", myDataTable.onEventUnhighlightCell);

			myDataTable.subscribe("cellClickEvent", function(oArgs)
			{
				var target = oArgs.target;
				var record = this.getRecord(target);
				var column = this.getColumn(target);
				var value = record.getData(column.key);
				var editorTypeCol = "editor_type";
				var itemID = record.getData("id");

				if(column.key != "value")
				{
					return;
				}

				if(record.getData(editorTypeCol) == "select")
				{
					column.editor = new YAHOO.widget.DropdownCellEditor({dropdownOptions : record.getData("available_values")});
				}
				else if(record.getData(editorTypeCol) == "integer")
				{
					column.editor = new YAHOO.widget.TextboxCellEditor({validator : YAHOO.widget.DataTable.validateNumber});
				}
				else if(record.getData(editorTypeCol) == "textarea")
				{
					column.editor = new YAHOO.widget.TextareaCellEditor();
				}
				else
				{
					column.editor = new YAHOO.widget.TextboxCellEditor();
				}

				column.editor.subscribe("saveEvent", function(argsEditor){ mfScripts.settingsTable.updateConfigValue(argsEditor.newData, itemID);  });
				this.onEventShowCellEditor(oArgs);
			});
			
			return {
				oDS: myDataSource,
				oDT: myDataTable
			};
		}();
		
		/* record updater */
		mfScripts.settingsTable.updateConfigValue = function(newValue, recordID)
		{
			<?php
			if(_CONFIG_DEMO_MODE == true)
			{
			?>
				alert(t("no_changes_in_demo_mode"));
			<?php
			}
			else
			{
			?>;
			var postData = "newValue="+urlEncode(newValue)+"&recordID="+recordID; 
			var callback =
			{
				success: mfScripts.settingsTable.handleUpdateSuccess,
				failure: mfScripts.settingsTable.handleUpdateFailure
			};
			var sUrl = "ajax/updateConfig.ajax.php";
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData); 
			<?php
			}
			?>
		};
		
		mfScripts.settingsTable.handleUpdateSuccess = function(o)
		{
			if(o.responseText !== undefined)
			{
				/* do something */
			}
		};
		
		mfScripts.settingsTable.handleUpdateFailure = function(o)
		{
			/* do something */
		};
		
	});
	</script>

<?php
}
else
{
	echo t("no_available_content");
}
?>

<?php
require_once('_footer.inc.php');
?>