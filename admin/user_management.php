<?php
require_once('_header.inc.php');

/* update any user status changes */
if ($r = (int) $_REQUEST['r'])
{
    $row = $db->getRow("SELECT status FROM users WHERE id = " . $r . " LIMIT 1");
    if ($row['status'] == "active")
    {
        $db->query("UPDATE users SET status='disabled' WHERE id = " . $r . " LIMIT 1");
    }
    else
    {
        $db->query("UPDATE users SET status='active' WHERE id = " . $r . " LIMIT 1");
    }
}
?>

<p><?php echo t("user_management_intro"); ?></p>

<div id="cellediting" class="yuiTable"></div>

<ul class="navLink" style="padding-top: 10px;">
    <li><a href="#" onClick="displayUserPopup(); return false;"><?php echo t("add_new_user"); ?></a></li>
</ul>

<?php
/* load in config options from db */
$rows = $db->getRows("SELECT users.*, (SELECT SUM(fileSize) FROM file WHERE file.userId=users.id AND file.statusId=1) AS totalFileSize, (SELECT COUNT(id) FROM file WHERE file.userId=users.id AND file.statusId=1) AS totalFiles FROM users ORDER BY username");
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
        $userAction = "<a href='#' onClick='return editUserPopup(" . $row['id'] . ");'>edit</a>";
        $userAction .= " | <a href='files.php?user=".$row['id']."'>" . t("view_files", "view files") . "</a>";
        if ($row['username'] != $userObj->username)
        {
            $userAction .= " | <a href='#' onClick='return changeUserState(" . $row['id'] . ");'>" . ($row['status'] == "active" ? t("disable") : t("enable")) . "</a>";
        }
        $formattedRow[] = "{id:\"" . $row['id'] . "\", username:\"" . addslashes($row['username']) . "\", email:\"" . addslashes($row['email']) . "\", level:\"" . addslashes($row['level']) . "\", lastlogin:\"" . dater($row['lastlogindate']) . "\", status:\"" . addslashes($row['status']) . "\", total_space_used:\"" . addslashes($row['totalFileSize']) . "\", raw_total_space_used:\"" . addslashes($row['totalFileSize']) . "\", total_files:" . (int)$row['totalFiles'] . ", user_action:\"" . $userAction . "\"}";
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
                                    {key:"username", label:t("username"), sortable:true},
                                    {key:"email", label:t("email_address"), sortable:true},
                                    {key:"level", label:t("account_type"), sortable:true},
                                    {key:"lastlogin", label:t("last_login"), sortable:true},
                                    {key:"status", label:t("account_status"), sortable:true},
                                    {key:"total_space_used", label:t("total_space_used"), sortable:true, formatter:YAHOO.widget.DataTable.formatSize},
                                    {key:"total_files", label:t("total_files"), sortable:true},
                                    {key:"user_action", label:t("action"), sortable:false}
                                ];

                                var myDataSource = new YAHOO.util.DataSource(mfScripts.settingsTable.Data.configData);
                                myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
                                myDataSource.responseSchema = {
                                    fields: ["id","username","email","level","lastlogin","status","total_space_used","total_files","user_action"]
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
    		
                            /* record updater */
                            mfScripts.settingsTable.updateConfigValue = function(newValue, recordID)
                            {
                                var postData = "newValue="+newValue+"&recordID="+recordID; 
                                var callback =
                                    {
                                    success: mfScripts.settingsTable.handleUpdateSuccess,
                                    failure: mfScripts.settingsTable.handleUpdateFailure,
                                    argument: ['foo','bar']
                                };
                                var sUrl = "ajax/updateConfig.ajax.php";
                                var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData); 
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

                        /* popup widget - add */
                        YAHOO.util.Event.addListener(window, "load", function(){
                            mfScripts.newUserPopup = new YAHOO.widget.Dialog("newUserPopup", 
                            {
                                width : "400px",
                                modal : true,
                                zindex : 4,
                                fixedcenter : true,
                                visible : false, 
                                constraintoviewport : true
                            });

                            mfScripts.newUserPopup.render(document.body);

                            new YAHOO.inputEx.Form({
                                parentEl: 'myFormContainer',
                                fields: [
                                    {
                                        type:'group',inputParams:
                                            {
                                            legend:'Login Details',name:'group1', className:'inputEx-Group-Custom', fields:
                                                [
                                                {inputParams: {label: t("username"), name: 'username', required: true } }, 
                                                {type: 'password', inputParams: {label: t("password"), name: 'password', required: true, strengthIndicator: true}}
                                            ]
                                        }
                                    },
                                    {
                                        type:'group',inputParams:
                                            {
                                            legend:'Account Details',name:'group2', className:'inputEx-Group-Custom', fields:
                                                [ 
                                                {type: 'select', inputParams: {label: t("account_status"), name: 'state', selectValues: ['active','pending','disabled','suspended'] } },
                                                {type: 'select', inputParams: {label: t("account_type"), name: 'accounttype', selectValues: ['free user','paid user', 'admin'] } },
                                                {inputParams: {label: t("optional_account_expiry"), name: 'accountexpiry', required: false, valueFormat: 'd-m-Y'}}
                                            ]
                                        }
                                    },
                                    {
                                        type:'group',inputParams:
                                            {
                                            legend:'User Details', name:'group3', className:'inputEx-Group-Custom', fields:
                                                [ 
                                                {type: 'select', inputParams: {label: t("title"), name: 'title', selectValues: ['Mr','Mrs','Miss','Ms','Dr'] } }, 
                                                {inputParams: {label: t("firstname"), name: 'firstname', required: true } }, 
                                                {inputParams: {label: t("lastname"), name: 'lastname', required: true } },
                                                {type: 'email', inputParams: {label: t("email_address"), name: 'email', required: true }}
                                            ]
                                        }
                                    }
                                ],
                                buttons: [{type: 'submit', value: 'Add User'}],
                                ajax: {
                                    method: 'POST',
                                    uri: 'ajax/addNewUser.ajax.php',
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
                                                mfScripts.newUserPopup.show();
                                                window.location = "user_management.php";
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
    		 
                        function displayUserPopup()
                        {
                            mfScripts.newUserPopup.show();
                        }
    	
                        /* popup widget - edit */
                        function editUserPopup(userId)
                        {
                            document.getElementById("myEditFormContainer").innerHTML = "";
                            mfScripts.editUserPopup = new YAHOO.widget.Dialog("editUserPopup", 
                            {
                                width : "400px",
                                modal : true,
                                zindex : 4,
                                fixedcenter : true,
                                visible : false, 
                                constraintoviewport : true
                            });

                            mfScripts.editUserPopup.render(document.body);
    		
                            /* get user details */
                            var postData = "id="+userId; 
                            var callback =
                                {
                                success: updateAndShowEditForm,
                                failure: failAjax
                            };
                            var sUrl = "ajax/getAdminData.ajax.php";
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
                                            legend:'Login Details',name:'group1', className:'inputEx-Group-Custom', fields:
                                                [
                                                {type: 'password', inputParams: {label: t("password"), name: 'password', strengthIndicator: true}}
                                            ]
                                        }
                                    },
                                    {
                                        type:'group',inputParams:
                                            {
                                            legend:'Account Details',name:'group2', className:'inputEx-Group-Custom', fields:
                                                [ 
                                                {type: 'select', inputParams: {label: t("account_status"), name: 'state', value: result.status, selectValues: ['active','pending','disabled','suspended'] } },
                                                {type: 'select', inputParams: {label: t("account_type"), name: 'accounttype', value: result.level, selectValues: ['free user','paid user', 'admin'] } },
                                                {inputParams: {label: t("optional_account_expiry"), name: 'accountexpiry', value: result.paidExpiryDate, required: false, valueFormat: 'd-m-Y'}}
                                            ]
                                        }
                                    },
                                    {
                                        type:'group',inputParams:
                                            {
                                            legend:'User Details', name:'group3', className:'inputEx-Group-Custom', fields:
                                                [ 
                                                {type: 'select', inputParams: {label: t("title"), name: 'title', value: result.title, selectValues: ['Mr','Mrs','Miss','Ms','Dr'] } }, 
                                                {inputParams: {label: t("firstname"), name: 'firstname', value: result.firstname, required: true } }, 
                                                {inputParams: {label: t("lastname"), name: 'lastname', required: true, value: result.lastname } },
                                                {type: 'email', inputParams: {label: t("email_address"), name: 'email', required: true, value: result.email }},
                                                {type: 'hidden', inputParams: {name: 'id', value: result.id }}
                                            ]
                                        }
                                    }
                                ],
                                buttons: [{type: 'submit', value: 'Edit User'}],
                                ajax: {
                                    method: 'POST',
                                    uri: 'ajax/editUser.ajax.php',
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
                                                mfScripts.editUserPopup.hide();
                                                window.location = "user_management.php";
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
    		
                            mfScripts.editUserPopup.show();
                        }
    	
                        function failAjax()
                        {
                            alert("Failed loading user information.");
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

<div id="newUserPopup" class="yui-pe-content">
    <div class="hd"><?php echo t("enter_user_details"); ?></div>
    <div class="bd">
        <div id='myFormContainer'></div>
    </div>
</div>

<div id="editUserPopup" class="yui-pe-content">
    <div class="hd"><?php echo t("enter_user_details"); ?></div>
    <div class="bd">
        <div id='myEditFormContainer'></div>
    </div>
</div>