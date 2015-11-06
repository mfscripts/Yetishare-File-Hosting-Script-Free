/* script specific admin js */

/* shortUrl Management Pages */
mfScripts.filesTable 				= {};
mfScripts.filesTable.oldPage 		= null;
mfScripts.filesTable.timeOutLoader 	= null;
mfScripts.filesTable.tableParams 	= null;

mfScripts.filesTable.updateFileStatus = function(id, statusId)
{
    var postData = "id="+id+"&statusId="+statusId;
    var callback =
    {
        success: mfScripts.filesTable.handleUpdateSuccess,
        failure: mfScripts.filesTable.handleUpdateFailure
    };
    var sUrl = "ajax/updateFileState.ajax.php";
    var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}

mfScripts.filesTable.handleUpdateSuccess = function(o)
{
    if(o.responseText !== undefined)
    {
        mfScripts.filesTable.oldPage = mfScripts.filesTable.tablePager.getCurrentPage();
        mfScripts.filesTable.DynamicData();
    }
};

mfScripts.filesTable.updatePager = function()
{
    if(mfScripts.filesTable.oldPage != null)
    {
        mfScripts.filesTable.tablePager.setPage(mfScripts.filesTable.oldPage, true);
    }
    mfScripts.filesTable.oldPage = null;
};

mfScripts.filesTable.handleUpdateFailure = function(o)
{
    /* do something */
    };

mfScripts.filesTable.DynamicData = function() {
    var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event;
		
    var formatUrl = function(elCell, oRecord, oColumn, sData) {
        elCell.innerHTML = "<a href='" + oRecord.getData("shortUrl") + "' title='" + oRecord.getData("originalFilename").replace( /'/g, '%27' ) + "' target='_blank'>" + sData + "</a>";
    };
	
    var formatStatus = function(elCell, oRecord, oColumn, sData) {
        var color = "red";
        if(sData == "active")
        {
            color = "green";
        }
        elCell.innerHTML = "<div class='urlStatus_"+color+"'>" + sData + "</div>";
    };
	
    var formatAction = function(elCell, oRecord, oColumn, sData) {
        var status = oRecord.getData("status");
        if(status == "active")
        {
            elCell.innerHTML = "<a href='" + oRecord.getData("shortUrl") + "' title='" + oRecord.getData("originalFilename").replace( /'/g, '%27' ) + "' target='_blank'>"+t("view", "view")+"</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='" + oRecord.getData("shortUrl") + "~s' target='_blank'>"+t("stats", "stats")+"</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='#' onClick=\"mfScripts.filesTable.updateFileStatus("+oRecord.getData("id")+", 3); return false;\">"+t("remove", "remove")+"</a>";
        }
        else
        {
            elCell.innerHTML = "<a href='" + oRecord.getData("shortUrl") + "~s' target='_blank'>"+t("stats", "stats")+"</a>";
        }
    };

    var myColumnDefs = [
    {
        key:"shortUrl",
        label:"Short Url",
        sortable:true,
        formatter:formatUrl
    },
    
    {
        key:"originalFilename",
        label:"Filename",
        sortable:true
    },
    
    {
        key:"fileSize",
        label:"Filesize",
        sortable:true
    },

    {
        key:"uploadedDate",
        label:"Date Uploaded",
        sortable:true
    },

    {
        key:"visits",
        label:"Total Downloads",
        formatter:YAHOO.widget.DataTable.formatNumber,
        sortable:true
    },

    {
        key:"uploadedIp",
        label:"Uploaded IP",
        sortable:true
    },

    {
        key:"lastAccessed",
        label:"Last Downloaded",
        sortable:true
    },

    {
        key:"status",
        label:"Status",
        formatter:formatStatus,
        sortable:true
    },

    {
        key:"actions",
        label:"Actions",
        formatter:formatAction,
        sortable:false
    }
    ];

    mfScripts.filesTable.mainDataSource = new YAHOO.util.DataSource("ajax/manageFiles.ajax.php?");
    mfScripts.filesTable.mainDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
    mfScripts.filesTable.mainDataSource.connXhrMode = "queueRequests";
    mfScripts.filesTable.mainDataSource.responseSchema = {
        resultsList: "records",
        fields: ["shortUrl","originalFilename","uploadedDate","visits","uploadedIp","lastAccessed","status","id","fileSize"],
        metaFields: {
            totalRecords: "totalRecords"
        }
    };
	
    var myRequestBuilder = function(oState, oSelf)
    {
        oState = oState || {
            pagination:null,
            sortedBy:null
        };
        var sort = (oState.sortedBy) ? oState.sortedBy.key : "uploadedDate";
        var dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
        var startIndex = (oState.pagination != null) ? oState.pagination.recordOffset : 0;
        var results = (oState.pagination != null) ? oState.pagination.rowsPerPage : 25;
        var filter = document.getElementById('files_search').value;
        var filterDisabled = document.getElementById('file_search_disabled').checked;
        var selE = document.getElementById("file_search_server");
        var filterServer = selE.options[selE.selectedIndex].value;
        var selEU = document.getElementById("file_search_user");
        var filterUser = selEU.options[selEU.selectedIndex].value;

        return 'output=json&filter='+ filter + '&filterDisabled='+filterDisabled+'&filterServer='+filterServer+'&filterUser='+filterUser+'&startIndex='+ startIndex + '&results=' + results + '&sort=' + sort + '&dir=' + dir;
    }
	
    /* setup default request params */
    if(mfScripts.filesTable.tableParams == null)
    {
        var selE = document.getElementById("file_search_server");
        var filterServer = selE.options[selE.selectedIndex].value;
        var selEU = document.getElementById("file_search_user");
        var filterUser = selEU.options[selEU.selectedIndex].value;
        
        mfScripts.filesTable.tableParams = "results=25&sort=uploadedDate&dir=DESC&output=json&startIndex=0&filter="+document.getElementById('files_search').value+"&filterDisabled="+document.getElementById('file_search_disabled').checked+'&filterServer='+filterServer+'&filterUser='+filterUser;
    }
	
    mfScripts.filesTable.tablePager = new YAHOO.widget.Paginator({
        rowsPerPage: 25,
        containers: 'paginatorContainer'
    });

    mfScripts.filesTable.mainDataTable = new YAHOO.widget.DataTable("dataTable", myColumnDefs,
        mfScripts.filesTable.mainDataSource, {
            initialRequest: mfScripts.filesTable.tableParams,
            dynamicData: true,
            paginator: mfScripts.filesTable.tablePager,
            generateRequest: myRequestBuilder
        });
			
    mfScripts.filesTable.mainDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
        if(typeof(oPayload) == "object")
        {
            mfScripts.filesTable.previousPayload = oPayload;
        }
        else
        {
            var oPayload = mfScripts.filesTable.previousPayload;
        }
        oPayload.totalRecords = oResponse.meta.totalRecords;
        mfScripts.filesTable.tableParams = oRequest;
        return oPayload;
    }
	
    mfScripts.filesTable.mainDataTable.subscribe('postRenderEvent', mfScripts.filesTable.updatePager);
    mfScripts.filesTable.mainDataTable.subscribe("rowMouseoverEvent", mfScripts.filesTable.mainDataTable.onEventHighlightRow);
    mfScripts.filesTable.mainDataTable.subscribe("rowMouseoutEvent", mfScripts.filesTable.mainDataTable.onEventUnhighlightRow);
    mfScripts.filesTable.mainDataTable.subscribe("rowClickEvent", mfScripts.filesTable.mainDataTable.onEventSelectRow);

    return {
        ds: mfScripts.filesTable.mainDataSource,
        dt: mfScripts.filesTable.mainDataTable
    };
};

mfScripts.filesTable.updateFilteredResults = function() {
    if(mfScripts.filesTable.timeOutLoader != null)
    {
        clearTimeout(mfScripts.filesTable.timeOutLoader);
    }
    mfScripts.filesTable.tableParams = null;
    mfScripts.filesTable.timeOutLoader = setTimeout("mfScripts.filesTable.DynamicData()", 500);
}

function urlEncode(s)
{
    return encodeURIComponent( s ).replace( /\%20/g, '+' ).replace( /!/g, '%21' ).replace( /'/g, '%27' ).replace( /\(/g, '%28' ).replace( /\)/g, '%29' ).replace( /\*/g, '%2A' ).replace( /\~/g, '%7E' );
}
   
function urlDecode(s)
{
    return decodeURIComponent( s.replace( /\+/g, '%20' ).replace( /\%21/g, '!' ).replace( /\%27/g, "'" ).replace( /\%28/g, '(' ).replace( /\%29/g, ')' ).replace( /\%2A/g, '*' ).replace( /\%7E/g, '~' ) );
}

function bytesToSize(bytes)
{
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};