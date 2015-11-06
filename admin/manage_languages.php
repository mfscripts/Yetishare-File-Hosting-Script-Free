<?php
require_once('_header.inc.php');
$languageId = (int)$_REQUEST['languageId'];
?>

<p>
<?php
if((int)$languageId)
{
	$languageName = $db->getValue("SELECT languageName FROM language WHERE id = ".(int)$languageId." LIMIT 1");
	echo t("manage_languages_intro_2");
}
else
{
	echo t("manage_languages_intro_1");
}
?>
</p>

<?php
if((int)$languageId)
{
?>
	<div id="languageEditing" class="yuiTable"></div>

	<?php
	/* make sure we have all content records populated */
	$getMissingRows = $db->getRows("SELECT id, languageKey, defaultContent FROM language_key WHERE id NOT IN (SELECT languageKeyId FROM language_content WHERE languageId = ".(int)$languageId.")");
	if(COUNT($getMissingRows))
	{
		foreach($getMissingRows AS $getMissingRow)
		{
			$dbInsert = new DBObject("language_content", array("languageKeyId", "languageId", "content"));
			$dbInsert->languageKeyId 	= $getMissingRow['id'];
			$dbInsert->languageId 		= (int)$languageId;
			$dbInsert->content 			= $getMissingRow['defaultContent'];
			$dbInsert->insert();
		}
	}

	/* load in language content from db */
	$rows = $db->getRows("SELECT language_content.id, language_content.content, language_key.languageKey, language_key.defaultContent FROM language_content LEFT JOIN language_key ON language_content.languageKeyId = language_key.id WHERE language_content.languageId = ".(int)$languageId);
	if(COUNT($rows) > 0)
	{
	?>
		<script>
		mfScripts.languageTable = {};

		mfScripts.languageTable.Data = {
			configData: [
				<?php
					$formattedRow = array();
					foreach($rows AS $row)
					{
						$formattedRow[] = "{id:\"".$row['id']."\", key:\"".str_replace("\n", "", addslashes($row['languageKey']))."\", value:\"".str_replace("\n", "", addslashes($row['content']))."\", editor_type:\"string\", group:\"".str_replace("\n", "", addslashes($row['defaultContent']))."\"}";
					}
					echo implode(",", $formattedRow);
				?>
			]
		};

		YAHOO.util.Event.addListener(window, "load", function()
		{
			mfScripts.languageTable.InlineCellEditing = function()
			{
				var myColumnDefs = [
					{key:"id", hidden:true},
					{key:"editor_type", hidden:true},
					{key:"key", label:t("language_key"), sortable:true},
					{key:"group", label:t("default_content"), sortable:true},
					{key:"value", label:t("translated_content"), editor: new YAHOO.widget.TextareaCellEditor(), sortable:true}
				];

				var myDataSource = new YAHOO.util.DataSource(mfScripts.languageTable.Data.configData);
				myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
				myDataSource.responseSchema = {
					fields: ["id","key","value","editor_type","group"]
				};

				var myDataTable = new YAHOO.widget.DataTable("languageEditing", myColumnDefs, myDataSource, {});

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

					column.editor = new YAHOO.widget.TextareaCellEditor();
					column.editor.subscribe("saveEvent", function(argsEditor){ mfScripts.languageTable.updateLanguageValue(argsEditor.newData, itemID); });
					this.onEventShowCellEditor(oArgs)
				});
				
				return {
					oDS: myDataSource,
					oDT: myDataTable
				};
			}();
			
			/* record updater */
			mfScripts.languageTable.updateLanguageValue = function(newValue, recordID)
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
				?>
				var postData = "newValue="+urlEncode(newValue)+"&recordID="+recordID; 
				var callback =
				{
					success: mfScripts.languageTable.handleUpdateSuccess,
					failure: mfScripts.languageTable.handleUpdateFailure
				};
				var sUrl = "ajax/updateLanguage.ajax.php";
				var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData); 
				<?php
				}
				?>
			};
			
			mfScripts.languageTable.handleUpdateSuccess = function(o)
			{
				if(o.responseText !== undefined)
				{
					/* do something */
				}
			};
			
			mfScripts.languageTable.handleUpdateFailure = function(o)
			{
				/* do something */
			};
			
		});
		</script>
		
		<ul style="padding-top: 10px;" class="navLink">
			<li><a href="manage_languages.php"><?php echo t("manage_other_languages"); ?></a></li>
		</ul>

	<?php
	}
	else
	{
		echo t("no_available_content");
	}
	?>
<?php
}
else
{
?>
	<form id="selectLanguageForm" name="selectLanguageForm" method="GET" action="manage_languages.php">
		<select name="languageId" id="languageId" onChange="mfScripts.languageSelector.submitSelection(); return false;">
			<option value=''><?php echo t("select_language"); ?>...</option>
			<?php
			/* make sure we have all content records populated */
			$getLanguages = $db->getRows("SELECT id, languageName FROM language ORDER BY languageName");
			if(COUNT($getLanguages))
			{
				foreach($getLanguages AS $getLanguage)
				{
					echo "<option value='".$getLanguage['id']."'>".$getLanguage['languageName']."</option>";
				}
			}
			?>
		</select>
	</form>
	
	<ul style="padding-top: 10px;" class="navLink">
		<li><a href="#" onClick="displayAddLanguagePopup(); return false;"><?php echo t("add_language"); ?></a></li>
	</ul>
	
	<script>
	mfScripts.languageSelector = {};
	mfScripts.languageSelector.submitSelection = function()
	{
		document.getElementById("selectLanguageForm").submit();
	};
	
	/* popup widget */
	YAHOO.util.Event.addListener(window, "load", function(){
		mfScripts.newLanguagePopup = new YAHOO.widget.Dialog("newLanguagePopup", 
			{
				width : "400px",
				modal : true,
				zindex : 4,
				fixedcenter : true,
				visible : false, 
				constraintoviewport : true
			});

		mfScripts.newLanguagePopup.render(document.body);

		new YAHOO.inputEx.Form({
			parentEl: 'myFormContainer',
			fields: [
					{type:'group',inputParams:
							{legend:'Language', name:'group1', className:'inputEx-Group-Custom', fields:
								[
									{inputParams: {label: t('language_name'), name: 'language_name', required: true } }
								]
							}
					}
					],
			buttons: [{type: 'submit', value: t('add_language')}],
			ajax: {
				method: 'POST',
				uri: 'ajax/addNewLanguage.ajax.php',
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
							window.location = "manage_languages.php";
						}
					},
					failure: function(o)
					{
						alert(t('error_submitting_form'));
					}
				},
				showMask: true
			}
		});
	});
		 
	function displayAddLanguagePopup()
	{
		mfScripts.newLanguagePopup.show();
	}
	</script>
<?php
}
?>

<?php
require_once('_footer.inc.php');
?>

<div id="newLanguagePopup" class="yui-pe-content">
	<div class="hd"><?php echo t("language_details"); ?></div>
	<div class="bd">
		<div class='demoContainer' id='myFormContainer'></div>
	</div>
</div>