<script>
var fileUrls = [];
var lastEle = null;
var startTime = null;
$(document).ready(function() {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        sequentialUploads: true,
        maxFileSize: <?php echo $maxUploadSize; ?>,
        <?php echo COUNT($acceptedFileTypes)?('acceptFileTypes: /(\\.|\\/)('.str_replace(".", "", implode("|", $acceptedFileTypes).')$/i,')):''; ?> maxNumberOfFiles: 50
    })
    .bind('fileuploadadd', function(e, data) {
        $('#fileupload #fileListingWrapper').removeClass('hidden');
        $('#fileupload #initialUploadSection').addClass('hidden');
        $('#fileUploadBadge').addClass('hidden');
        
        // fix for safari
        getTotalRows();
        // end safari fix
        
        totalRows = getTotalRows()+1;
        updateTotalFilesText(totalRows);
		
    })
    .bind('fileuploadstart', function(e, data) {
        // hide/show sections
        $('#addFileRow').addClass('hidden');
        $('#processQueueSection').addClass('hidden');
        $('#processingQueueSection').removeClass('hidden');
        
        // set all cancel icons to processing
        $('.cancel').html('<img class="processingIcon" src="<?php echo SITE_IMAGE_PATH; ?>/processing_small.gif" width="16" height="16"/>');
        
        // set timer
        startTime = (new Date()).getTime();
    })
    .bind('fileuploadstop', function(e, data) {
        // finished uploading
        updateTitleWithProgress(100);
        updateProgessText(100, data.total, data.total);
        $('#processQueueSection').addClass('hidden');
        $('#processingQueueSection').addClass('hidden');
        $('#completedSection').removeClass('hidden');

        // set all remainging pending icons to failed
        $('.processingIcon').parent().html('<img src="<?php echo SITE_IMAGE_PATH; ?>/red_error_small.png" width="16" height="16"/>');

        // setup copy link
        setupCopyAllLink();
    })
    .bind('fileuploadprogressall', function(e, data) {
        // update page title with progress
        var progress = parseInt(data.loaded / data.total * 100, 10);
        updateTitleWithProgress(progress);
        updateProgessText(progress, data.loaded, data.total);
    })
    .bind('fileuploaddone', function(e, data) {
        // keep a copy of the urls globally
        fileUrls.push(data['result'][0]['url']);
    })
    .bind('fileuploadfail', function(e, data) {
        totalRows = getTotalRows();
        if(totalRows > 0)
        {
            totalRows = totalRows-1;
        }

        // if no items, show the original uploader
        if(totalRows == 0)
        {
            $('#fileupload #initialUploadSection').removeClass('hidden');
            $('#fileupload #fileListingWrapper').addClass('hidden');
            $('#fileUploadBadge').removeClass('hidden');
        }
        else
        {
            updateTotalFilesText(totalRows);
        }
    });

    // Open download dialogs via iframes,
    // to prevent aborting current uploads:
    $('#fileupload #files a:not([target^=_blank])').live('click', function (e) {
        e.preventDefault();
        $('<iframe style="display:none;"></iframe>')
            .prop('src', this.href)
            .appendTo('body');
    });

    //$(".ui-dialog-buttonpane").html("<div class='btn_bar_left'><div class='fileupload-progressbar'></div></div>"+$(".ui-dialog-buttonpane").html());
});

function setupCopyAllLink()
{
    // setup copy to clipboard
    $('#copyAllLink').zclip({
        path: "js/ZeroClipboard.swf",
        copy: getUrlsAsText()
    });
}

function updateProgessText(progress, uploadedBytes, totalBytes)
{
    // calculate speed & time left
    nowTime = (new Date()).getTime();
    loadTime = (nowTime - startTime);
    if(loadTime == 0)
    {
        loadTime = 1;
    }
    loadTimeInSec = loadTime/1000;
    bytesPerSec = uploadedBytes / loadTimeInSec;

    textContent = '';
    textContent += 'Progress: '+progress+'%';
    textContent += ' ';
    textContent += '('+bytesToSize(uploadedBytes, 2)+' / '+bytesToSize(totalBytes, 2)+')';
    
    $("#fileupload-progresstextLeft").html(textContent);
    
    rightTextContent = '';
    rightTextContent += 'Speed: '+bytesToSize(bytesPerSec, 2)+'ps. ';
    rightTextContent += 'Remaining: '+humanReadableTime((totalBytes/bytesPerSec)-(uploadedBytes/bytesPerSec));
    
    $("#fileupload-progresstextRight").html(rightTextContent);
}

function getUrlsAsText()
{
    urlStr = '';
    for(var i=0; i<fileUrls.length; i++)
    {
        urlStr += fileUrls[i]+'\n';
    }

    return urlStr;
}

function updateTitleWithProgress(progress)
{
    if(typeof(progress) == "undefined")
    {
        var progress = 0;
    }
    if(progress == 0)
    {
        $(document).attr("title", "<?php echo PAGE_NAME; ?> - <?php echo SITE_CONFIG_SITE_NAME; ?>");
    }
    else
    {
        $(document).attr("title", progress+"% Uploaded - <?php echo PAGE_NAME; ?> - <?php echo SITE_CONFIG_SITE_NAME; ?>");
    }
}

function getTotalRows()
{
    totalRows = $('#files .template-upload').length;
    if(typeof(totalRows) == "undefined")
    {
        return 0;
    }

    return totalRows;
}

function updateTotalFilesText(total)
{
    // removed for now, might be useful in some form in the future
    //$('#uploadButton').html('upload '+total+' files');
}

function setRowClasses()
{
    // removed for now, might be useful in some form in the future
    //$('#files tr').removeClass('even');
    //$('#files tr').removeClass('odd');
    //$('#files tr:even').addClass('odd');
    //$('#files tr:odd').addClass('even');
}

function showAdditionalInformation(ele)
{
	// block parent clicks from being processed on additional information
	$('.sliderContent table').unbind();
	$('.sliderContent table').click(function(e){
		e.stopPropagation();
	});
	
    // make sure we've clicked on a new element
    if(lastEle == ele)
    {
        // close any open sliders
        $('.sliderContent').slideUp('fast');
        // remove row highlighting
        $('.sliderContent').parent().parent().removeClass('rowSelected');
        lastEle = null;
        return false;
    }
    lastEle = ele;

    // close any open sliders
    $('.sliderContent').slideUp('fast');

    // remove row highlighting
    $('.sliderContent').parent().parent().removeClass('rowSelected');

    // select row and popup content
    $(ele).addClass('rowSelected');

    // set the position of the sliderContent div
    $(ele).find('.sliderContent').css('left', $(ele).offset().left);
    $(ele).find('.sliderContent').css('top', $(ele).offset().top+38);
    $(ele).find('.sliderContent').slideDown(400, function() {
    });

    return false;
}

function saveFileToFolder(ele)
{
    // get variables
    shortUrl = $(ele).closest('.sliderContent').children('.shortUrlHidden').val();
    folderId = $(ele).val();
    
    // send ajax request
    var request = $.ajax({
        url: "<?php echo _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FULL_URL; ?>/_updateFolder.ajax.php",
        type: "POST",
        data: {shortUrl: shortUrl, folderId: folderId},
        dataType: "html"
    });
}
</script>
