<?php
require_once('_header.inc.php');
require_once('admin_libraries/charts/Code/PHPClass/Includes/FusionCharts.php');

/* setup colours */
$colours = explode("|", "B02B2C|D15600|C79810|73880A|6BBA70|3F4C6B|356AA0|D01F3C");
?>

<script language="javascript" src="admin_libraries/charts/JSClass/FusionCharts.js"></script>

<p><?php echo t("dashboard_intro"); ?></p>

<div class="dashboard14Days">
	<?php

	/* last 14 days chart */
	$tracker = 14;
	$last7Days = array();
	while($tracker >= 0)
	{
		$date = date("Y-m-d", strtotime("-".$tracker." day"));
		$last7Days[$date] = 0;
		$tracker--;
	}

	$minY = 0;
	$maxY = 10;
	foreach($last7Days AS $k=>$total)
	{
		$totalUrls = $db->getValue("SELECT COUNT(id) AS total FROM file WHERE MID(uploadedDate, 1, 10) = '".$k."'");
		$last7Days[$k] = (int)$totalUrls;
		if($totalUrls > $maxY)
		{
			$maxY = $totalUrls;
		}
	}

	$strXML  = "";
	$strXML .= "<graph caption='".t("dashboard_graph_last_14_days_title", "Last 14 days file uploads")."' yAxisMinValue='".$minY."' yAxisMaxValue='".$maxY."' canvasBorderThickness='1' showValues='0' canvasBgColor='dddddd' divlinecolor='ffffff' canvasBorderColor='cccccc' rotateNames='0' animation='1' xAxisName='Day' yAxisName='".UCWords(t("files", "files"))."' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='12'>";
	foreach($last7Days AS $k=>$total)
	{
		$position = rand(0, COUNT($colours)-1);
		$strXML .= "<set name='".dater($k, "jS")."' value='".$total."' color='".$colours[$position]."'/>";
	}
	$strXML .= "</graph>";

	echo renderChartHTML("admin_libraries/charts/Charts/FCF_Column2D.swf", "", $strXML, "weekChart", 600, 300);

	?>
</div>

<div class="dashboardPie">
	<?php

	/* pie chart of the status of urls */
	$dataForPie = $db->getRows("SELECT COUNT(file.id) AS total, file_status.label AS status FROM file LEFT JOIN file_status ON file.statusId = file_status.id GROUP BY file.statusId");

	$strXML  = "";
	$strXML .= "<graph animation='1' showValues='0' showNames='1' pieRadius='95' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='10'>";
	foreach($dataForPie AS $dataRow)
	{
		$position = rand(0, COUNT($colours)-1);
		$strXML .= "<set name='".UCWords(t($dataRow['status'], $dataRow['status']))."' value='".$dataRow['total']."' color='".$colours[$position]."'/>";
	}
	$strXML .= "</graph>";

	echo renderChartHTML("admin_libraries/charts/Charts/FCF_Pie2D.swf", "", $strXML, "statusPie", 320, 250);

	?>
</div>

<div class="clear"><!-- --></div>

<div class="dashboard12Months">
	<?php

	/* last 12 months chart */
	$tracker = 12;
	$last7Days = array();
	while($tracker >= 0)
	{
		$date = date("Y-m", strtotime("-".$tracker." month"));
		$last7Days[$date] = 0;
		$tracker--;
	}
	
	$minY = 0;
	$maxY = 10;
	foreach($last7Days AS $k=>$total)
	{
		$totalUrls = $db->getValue("SELECT COUNT(id) AS total FROM file WHERE MID(uploadedDate, 1, 7) = '".$k."'");
		$last7Days[$k] = (int)$totalUrls;
		if($totalUrls > $maxY)
		{
			$maxY = $totalUrls;
		}
	}

	$strXML  = "";
	$strXML .= "<graph caption='".t("dashboard_graph_last_12_months_title", "Last 12 months file uploads")."' yAxisMinValue='".$minY."' yAxisMaxValue='".$maxY."' canvasBorderThickness='1' showValues='0' canvasBgColor='dddddd' divlinecolor='ffffff' canvasBorderColor='cccccc' rotateNames='0' animation='1' xAxisName='Month' yAxisName='".UCWords(t("files", "files"))."' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='12'>";
	foreach($last7Days AS $k=>$total)
	{
		$position = rand(0, COUNT($colours)-1);
		$strXML .= "<set name='".dater($k, "M")."' value='".$total."' color='".$colours[$position]."'/>";
	}
	$strXML .= "</graph>";

	echo renderChartHTML("admin_libraries/charts/Charts/FCF_Column2D.swf", "", $strXML, "yearChart", 600, 300);

	?>
</div>

<div class="dashboardDataTable">
	<div id="dashboardOverviewTable" class="yuiTable"></div>
	<script>
	mfScripts.dashboardOverviewTable = {};
	mfScripts.dashboardOverviewTable.Data = {
		configData: [
			<?php
				$db = Database::getDatabase(true);
				$formattedRow = array();
				/* total active */
				$totalActive = $db->getValue("SELECT COUNT(id) AS total FROM file WHERE statusId = 1");
				$formattedRow[] = "{name:\"<div style='width:230px;'>".t("dashboard_total_active_files", "Total Active Files")."</div>\", name_value:\"".(int)$totalActive."\"}";
				/* total disabled */
				$totalInActive = $db->getValue("SELECT COUNT(id) AS total FROM file WHERE statusId != 1");
				$formattedRow[] = "{name:\"".t("dashboard_total_disabled_files", "Total Inactive Files")."\", name_value:\"".(int)$totalInActive."\"}";
				/* total visits */
				$totalVisits = $db->getValue("SELECT SUM(visits) AS total FROM file");
				$formattedRow[] = "{name:\"".t("dashboard_total_downloads_to_all", "Total Downloads")."\", name_value:\"".(int)$totalVisits."\"}";
				echo implode(",", $formattedRow);
			?>
		]
	};

	YAHOO.util.Event.addListener(window, "load", function()
	{
		mfScripts.dashboardOverviewTable.tableStructure = function()
		{
			var myColumnDefs = [
				{key:"name", label:t("item_name")},
				{key:"name_value", label:t("value")}
			];

			var myDataSource = new YAHOO.util.DataSource(mfScripts.dashboardOverviewTable.Data.configData);
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
			myDataSource.responseSchema = {
				fields: ["name","name_value"]
			};

			var myDataTable = new YAHOO.widget.DataTable("dashboardOverviewTable", myColumnDefs, myDataSource, {selectionMode:"single"});

			myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
			myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
			
			return {
				oDS: myDataSource,
				oDT: myDataTable
			};
		}();
		
	});
	</script>
</div>

<div class="clear"><!-- --></div>

<?php
require_once('_footer.inc.php');
?>