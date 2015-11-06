var bgFill = false;
$(document).ready(function(){
    $(".uiStyle").addClass("text ui-widget-content ui-corner-all");
    $("button").addClass("ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only");
    $("button, input:submit, input:reset").button();
    $("#loginLink").click(function(){
        bgFill = false;
        if($("#loginLinkWrapper").hasClass("loginLink"))
        {
            bgFill = true;
            $("#loginLinkWrapper").removeClass("loginLink");
            $("#loginLinkWrapper").addClass("loginLinkSelected");
        }
        
        $("#loginPanel").slideToggle(250, function()
        {
            if($("#loginLinkWrapper").hasClass("loginLink") == false)
            {
                if(bgFill == false)
                {
                    $("#loginLinkWrapper").addClass("loginLink");
                    $("#loginLinkWrapper").removeClass("loginLinkSelected");
                }

                $("#loginUsername").focus();
            }
        });
        return false;
    })
});

$(document).keydown(function(e) {
    if (e.keyCode == 27) {
        $("#loginLink").click();
    }
});

function bookmarksite(title, url)
{
	if (window.sidebar) // firefox
		window.sidebar.addPanel(title, url, "");
	else if(window.opera && window.print){ // opera
		var elem = document.createElement('a');
		elem.setAttribute('href',url);
		elem.setAttribute('title',title);
		elem.setAttribute('rel','sidebar');
		elem.click();
	} 
	else if(document.all)// ie
		window.external.AddFavorite(url, title);
}

function showHideStatsTab(id)
{
    if($("currentTab").value.length > 0)
    {
            $($("currentTab").value).style.display = "none";
    }
    $("currentTab").value = id;
    $(id).style.display = "block";
    return false;
}

function showHideTip(ele)
{
    $('.formTip').addClass('hidden');
    $('#'+ele.id+'Tip').removeClass('hidden');
}

function bytesToSize(bytes, precision)
{  
    var kilobyte = 1024;
    var megabyte = kilobyte * 1024;
    var gigabyte = megabyte * 1024;
    var terabyte = gigabyte * 1024;
   
    if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' B';
 
    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' KB';
 
    } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return (bytes / megabyte).toFixed(precision) + ' MB';
 
    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return (bytes / gigabyte).toFixed(precision) + ' GB';
 
    } else if (bytes >= terabyte) {
        return (bytes / terabyte).toFixed(precision) + ' TB';
 
    } else {
        return bytes + ' B';
    }
}

function humanReadableTime(seconds)
{
    var numhours = Math.floor(((seconds % 31536000) % 86400) / 3600);
    var numminutes = Math.floor((((seconds % 31536000) % 86400) % 3600) / 60);
    var numseconds = Math.floor((((seconds % 31536000) % 86400) % 3600) % 60);
    
    rs = '';
    if(numhours > 0)
    {
        rs += numhours + " hour";
        if(numhours != 1)
        {
            rs += "s";
        }
        rs += " ";
    }
    if(numminutes > 0)
    {
        rs += numminutes + " minute";
        if(numminutes != 1)
        {
            rs += "s";
        }
        rs += " ";
    }
    rs += numseconds + " second";
    if(numseconds != 1)
    {
        rs += "s";
    }
    
    return rs;
}