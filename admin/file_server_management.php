<?php
require_once('_header.inc.php');
?>

<p><?php echo t("file_server_management_intro", "Double click on any of the servers below to edit."); ?></p>

<div id="cellediting" class="yuiTable"></div>

<ul class="navLink" style="padding-top: 10px;">
    <li><a href="#" onClick="displayServerPopup(); return false;"><?php echo t("add_new_server", "Add new server"); ?></a></li>
</ul>

<?php
/* load in servers data */
$sQL  = "SELECT file_server.*, file_server_status.label AS statusLabel, (SELECT SUM(file.fileSize) FROM file WHERE file.serverId = file_server.id AND file.statusId = 1) AS totalFileSize, (SELECT COUNT(file.id) FROM file WHERE file.serverId = file_server.id AND file.statusId = 1) AS totalFiles ";
$sQL .= "FROM file_server ";
$sQL .= "LEFT JOIN file_server_status ON file_server.statusId = file_server_status.id ";
$sQL .= "LEFT JOIN file ON file_server.id = file.serverId ";
$sQL .= "GROUP BY file_server.serverLabel ";
$sQL .= "ORDER BY file_server.serverLabel";
$rows = $db->getRows($sQL);
if (COUNT($rows) > 0)
{
    ?>
    <script>
        mfScripts.settingsTable = {};

        mfScripts.settingsTable.Data = {
            configData: [
    <?php
    $formattedRow = array();
    foreach ($rows AS $row)
    {
        $action = '';

        // block the default from being edited
        if ($row['id'] != 1)
        {
            $action = "<a href='#' onClick='return editServerPopup(" . $row['id'] . ");'>edit</a>";
        }

        // only show test ftp link on remote servers
        if ($row['serverType'] == 'remote')
        {
            $action .= " | <a href='file_server_test_ftp.php?s=" . $row['id'] . "'>test ftp</a>";
        }
        $formattedRow[] = "{id:\"" . $row['id'] . "\", serverLabel:\"" . addslashes($row['serverLabel']) . "\", serverType:\"" . addslashes($row['serverType']) . "\", ipAddress:\"" . addslashes($row['ipAddress']) . "\", statusLabel:\"" . addslashes($row['statusLabel']) . "\", storagePath:\"" . addslashes($row['storagePath']) . "\", totalFileSize:\"" . addslashes($row['totalFileSize']) . "\", totalFiles:" . (int)$row['totalFiles'] . ", action:\"" . $action . "\"}";
    }
    echo implode(",", $formattedRow);
    ?>
                        ]
                    };

                    YAHOO.util.Event.addListener(window, "load", function()
                    {
                        mfScripts.settingsTable.InlineCellEditing = function()
                        {
                            YAHOO.widget.DataTable.formatSize = function(elLiner, oRecord, oColumn, oData) {
                                    elLiner.innerHTML = bytesToSize(oData);
                                };
                                
                            var myColumnDefs = [
                                {key:"id", hidden:true},
                                {key:"serverLabel", label:"<?php echo UCWords(t("server_label", "server label")); ?>", sortable:true},
                                {key:"serverType", label:"<?php echo UCWords(t("server_type", "server type")); ?>", sortable:true},
                                {key:"ipAddress", label:"<?php echo UCWords(t("ip_address", "ip address")); ?>", sortable:true},
                                {key:"statusLabel", label:"<?php echo UCWords(t("status", "status")); ?>", sortable:true},
                                {key:"storagePath", label:"<?php echo UCWords(t("storage_path", "storage path")); ?>", sortable:true},
                                {key:"totalFileSize", label:"<?php echo UCWords(t("total_space_used", "total space used")); ?>", sortable:true, formatter:YAHOO.widget.DataTable.formatSize},
                                {key:"totalFiles", label:"<?php echo UCWords(t("total_files", "total files")); ?>", sortable:true},
                                {key:"action", label:"<?php echo UCWords(t("action", "action")); ?>", sortable:false}
                            ];

                            var myDataSource = new YAHOO.util.DataSource(mfScripts.settingsTable.Data.configData);
                            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
                            myDataSource.responseSchema = {
                                fields: ["id","serverLabel","serverType","ipAddress","statusLabel","storagePath","totalFileSize","totalFiles","action"]
                            };

                            var myDataTable = new YAHOO.widget.DataTable("cellediting", myColumnDefs, myDataSource, {selectionMode:"single"});

                            myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
                            myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
                            myDataTable.subscribe("rowClickEvent", myDataTable.onEventSelectRow);
                            //myDataTable.subscribe("rowDblclickEvent", function() { editUserPopup(); });
    			
                            return {
                                oDS: myDataSource,
                                oDT: myDataTable
                            };
                        }();		
                    });

                    /* popup widget - add */
                    YAHOO.util.Event.addListener(window, "load", function(){
                        mfScripts.newServerPopup = new YAHOO.widget.Dialog("newServerPopup", 
                        {
                            width : "400px",
                            modal : true,
                            zindex : 4,
                            fixedcenter : true,
                            visible : false, 
                            constraintoviewport : true
                        });

                        mfScripts.newServerPopup.render(document.body);

                        new YAHOO.inputEx.Form({
                            parentEl: 'myFormContainer',
                            fields: [
                                {
                                    type:'group',inputParams:
                                        {
                                        legend:'Server Details',name:'group1', className:'inputEx-Group-Custom', fields:
                                            [ 
                                            {inputParams: {label: t("server_label"), name: 'serverLabel', required: true } },
                                            {type: 'select', inputParams: {label: t("server_type"), name: 'serverType', selectValues: ['local','remote'] } },
                                            {type: 'select', inputParams: {label: t("status"), name: 'status', selectValues: ['disabled','active','read only'] } }
                                        ]
                                    }
                                },
                                {
                                    type:'group',inputParams:
                                        {
                                        legend:'FTP Details (for remote server types)',name:'group2', className:'inputEx-Group-Custom', fields:
                                            [
                                            {inputParams: {label: t("ftp_host"), name: 'ipAddress', required: false } },
                                            {inputParams: {label: t("ftp_port"), name: 'ftpPort', required: false, value: '21' } },
                                            {inputParams: {label: t("username"), name: 'ftpUsername', required: false } }, 
                                            {type: 'password', inputParams: {label: t("password"), name: 'ftpPassword', required: false}}
                                        ]
                                    }
                                },
                                {
                                    type:'group',inputParams:
                                        {
                                        legend:'Other Details', name:'group3', className:'inputEx-Group-Custom', fields:
                                            [ 
                                            {inputParams: {label: t("storage_path"), name: 'storagePath', required: false } }
                                        ]
                                    }
                                }
                            ],
                            buttons: [{type: 'submit', value: 'Add Server'}],
                            ajax: {
                                method: 'POST',
                                uri: 'ajax/addNewServer.ajax.php',
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
                                            mfScripts.newServerPopup.show();
                                            window.location = "file_server_management.php";
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
    		 
                    function displayServerPopup()
                    {
                        mfScripts.newServerPopup.show();
                    }
    	
                    /* popup widget - edit */
                    function editServerPopup(serverId)
                    {
                        document.getElementById("myEditFormContainer").innerHTML = "";
                        mfScripts.editServerPopup = new YAHOO.widget.Dialog("editServerPopup", 
                        {
                            width : "400px",
                            modal : true,
                            zindex : 4,
                            fixedcenter : true,
                            visible : false, 
                            constraintoviewport : true
                        });

                        mfScripts.editServerPopup.render(document.body);
    		
                        /* get user details */
                        var postData = "id="+serverId; 
                        var callback =
                            {
                            success: updateAndShowEditForm,
                            failure: failAjax
                        };
                        var sUrl = "ajax/getAdminDataServer.ajax.php";
                        var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData); 
                    }
    	
                    function updateAndShowEditForm(data)
                    {
                        /* prepare result */
                        result = YAHOO.lang.JSON.parse(data.responseText);
                        new YAHOO.inputEx.Form({
                            parentEl: 'myEditFormContainer',
                            fields: [
                                {
                                    type:'group',inputParams:
                                        {
                                        legend:'Server Details',name:'group1', className:'inputEx-Group-Custom', fields:
                                            [ 
                                            {inputParams: {label: t("server_label"), value: result.serverLabel, name: 'serverLabel', required: true } },
                                            {type: 'select', inputParams: {label: t("server_type"), value: result.serverType, name: 'serverType', selectValues: ['local','remote'] } },
                                            {type: 'select', inputParams: {label: t("status"), value: result.status, name: 'status', selectValues: ['disabled','active','read only'] } }
                                        ]
                                    }
                                },
                                {
                                    type:'group',inputParams:
                                        {
                                        legend:'FTP Details (for remote server types)',name:'group2', className:'inputEx-Group-Custom', fields:
                                            [
                                            {inputParams: {label: t("ftp_host"), value: result.ipAddress, name: 'ipAddress', required: false } },
                                            {inputParams: {label: t("ftp_port"), value: result.ftpPort, name: 'ftpPort', required: false } },
                                            {inputParams: {label: t("username"), value: result.ftpUsername, name: 'ftpUsername', required: false } }, 
                                            {type: 'password', inputParams: {label: t("password"), value: result.ftpPassword, name: 'ftpPassword', required: false}}
                                        ]
                                    }
                                },
                                {
                                    type:'group',inputParams:
                                        {
                                        legend:'Other Details', name:'group3', className:'inputEx-Group-Custom', fields:
                                            [ 
                                            {inputParams: {label: t("storage_path"), value: result.storagePath, name: 'storagePath', required: false } },
                                            {type: 'hidden', inputParams: {name: 'id', value: result.id }}
                                        ]
                                    }
                                }
                            ],
                            buttons: [{type: 'submit', value: 'Edit Server'}],
                            ajax: {
                                method: 'POST',
                                uri: 'ajax/editServer.ajax.php',
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
                                            mfScripts.editServerPopup.hide();
                                            window.location = "file_server_management.php";
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
    		
                        mfScripts.editServerPopup.show();
                    }
    	
                    function failAjax()
                    {
                        alert("Failed loading server information.");
                    }
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

<div id="newServerPopup" class="yui-pe-content">
    <div class="hd"><?php echo t("enter_server_details", "Enter Server Details"); ?></div>
    <div class="bd">
        <div id='myFormContainer'></div>
    </div>
</div>

<div id="editServerPopup" class="yui-pe-content">
    <div class="hd"><?php echo t("enter_server_details", "Enter Server Details"); ?></div>
    <div class="bd">
        <div id='myEditFormContainer'></div>
    </div>
</div>