/* setup name space */
var mfScripts = {};

(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event;

    Event.onDOMReady(function() {
        var layout = new YAHOO.widget.Layout({
            units: [
                { position: 'top', height: 81, body: 'adminHeaderContainer', collapse: false, resize: false },
                { position: 'bottom', height: 34, resize: false, body: 'adminFooterContainer', collapse: false },
                { position: 'center', body: 'adminBody', scroll: true }
            ]
        });
		layout.on('render', function()
		{
            setupToolbar();
        });
        layout.render();
    });
})();


function setupToolbar()
{
	var oButton6 = new YAHOO.widget.Button({
		id: "button_logout", 
		type: "push", 
		label: t("logout"),
		onclick: { fn: function() { window.location="logout.php"; } },
		container: "toolbar" 
	});
	
	var oButton8 = new YAHOO.widget.Button({
		id: "button_languages", 
		type: "push", 
		label: t("languages"),
		onclick: { fn: function() { window.location="manage_languages.php"; } },
		container: "toolbar" 
	});
	
	var oButton4 = new YAHOO.widget.Button({
		id: "button_settings", 
		type: "push", 
		label: t("site_settings"), 
		onclick: { fn: function() { window.location="settings.php"; } },
		container: "toolbar" 
	});
	
	var oButton9 = new YAHOO.widget.Button({
		id: "button_banned_ips", 
		type: "push", 
		label: t("banned_ips"),
		onclick: { fn: function() { window.location="banned_ips.php"; } },
		container: "toolbar" 
	});
	
	var oButton1 = new YAHOO.widget.Button({
		id: "button_reply", 
		type: "push", 
		label: t("home"),
		onclick: { fn: function() { window.location="index.php"; } },
		container: "toolbar"
	});
	
	var oButton10 = new YAHOO.widget.Button({
		id: "button_manage_files", 
		type: "push", 
		label: t("manage_files"),
		onclick: { fn: function() { window.location="files.php"; } },
		container: "toolbar" 
	});
        
        var oButton7 = new YAHOO.widget.Button({
		id: "button_users",
		type: "push",
		label: t("admin_users"),
		onclick: { fn: function() { window.location="user_management.php"; } },
		container: "toolbar"
	});
        
        var oButton20 = new YAHOO.widget.Button({
		id: "button_servers",
		type: "push",
		label: t("admin_file_servers"),
		onclick: { fn: function() { window.location="file_server_management.php"; } },
		container: "toolbar"
	});
}

/* helper function for console.log */
if(typeof(console) != "undefined")
{
	var bug = console.log;
}

/* banned IPs */
function removeBannedIP(id)
{
	if(confirm(t("are_you_sure_you_want_to_remove_this_ip_ban")))
	{
		window.location = "banned_ips.php?r="+id;
	}
	else
	{
		return false;
	}
}

/* admin user management */
function changeUserState(id)
{
	if(confirm(t("are_you_sure_update_user_status")))
	{
		window.location = "user_management.php?r="+id;
	}
	else
	{
		return false;
	}
}