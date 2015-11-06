<?php
/* setup includes */
require_once('includes/master.inc.php');

/* fusion charts php class */
require_once('js/fusionCharts/Code/PHPClass/Includes/FusionCharts.php');

/* setup page */
define("PAGE_NAME", t("stats_page_name", "View file statistics"));
define("PAGE_DESCRIPTION", t("stats_meta_description", "Uploaded file statistics"));
define("PAGE_KEYWORDS", t("stats_meta_keywords", "stats, statistics, unique, visitors, hits, file, upload"));

$file = null;
if (isset($_REQUEST['u']))
{
    // only keep the initial part if there's a forward slash
    $shortUrl = current(explode("/", str_replace("~s", "", $_REQUEST['u'])));
    $file = file::loadByShortUrl($shortUrl);
}

/* load file details */
if(!$file)
{
    /* if no file found, redirect to home page */
    redirect(WEB_ROOT . "/index." . SITE_CONFIG_PAGE_EXTENSION);
}

require_once('_header.php');

/* setup colours */
$colours = explode("|", "B02B2C|D15600|C79810|73880A|6BBA70|3F4C6B|356AA0|D01F3C");
?>

<script src="js/yui_combo.js" type="text/javascript"></script>

<div class="statsHeaderWrapper">
	<div class="statsHeader" style="background: url(<?php echo SITE_IMAGE_PATH; ?>/stats/stats_head.png) no-repeat;">
		<div class="rightTotalVisits">
			<div class="visits">
				<?php echo $file->visits; ?>
			</div>
			<div class="label">
				<?php echo t("downloads", "downloads"); ?>:
			</div>
		</div>
		<div class="leftShortUrlDetails">
			<a href="<?php echo $file->getFullShortUrl(); ?>" target="_blank"><?php echo $file->originalFilename; ?></a>&nbsp;&nbsp;<a href="<?php echo $file->getShortInfoUrl(); ?>">(file details)</a><br/>
			<?php echo t("uploaded", "Uploaded"); ?> <?php echo dater($file->uploadedDate); ?>
		</div>
	</div>
</div>

<div class="statsBoxWrapper">
	<div id="demo" class="yui-navset">
		<ul class="yui-nav">
			<li class="selected"><a href="#tab1"><em><?php echo t("visitors", "visitors"); ?></em></a></li>
			<li><a href="#tab2"><em><?php echo t("countries", "countries"); ?></em></a></li>
			<li><a href="#tab3"><em><?php echo t("top_referrers", "top referrers"); ?></em></a></li>
			<li><a href="#tab4"><em><?php echo t("browsers", "browsers"); ?></em></a></li>
			<li><a href="#tab5"><em><?php echo t("operating_systems", "operating systems"); ?></em></a></li>
		</ul>            
		<div class="yui-content">
		
			<div>
				<!-- TAB 1 -->
				<br/>
				<a href="#" onClick="$('#tab1_chart1').show(); $('#tab1_chart2').hide(); $('#tab1_chart3').hide(); $('#tab1_chart4').hide(); return false;"><?php echo t("last_24_hours", "last 24 hours"); ?></a> | <a href="#" onClick="$('#tab1_chart2').show(); $('#tab1_chart1').hide(); $('#tab1_chart3').hide(); $('#tab1_chart4').hide(); return false;"><?php echo t("last_7_days", "last 7 days"); ?></a> | <a href="#" onClick="$('#tab1_chart3').show(); $('#tab1_chart2').hide(); $('#tab1_chart1').hide(); $('#tab1_chart4').hide(); return false;"><?php echo t("last_30_days", "last 30 days"); ?></a> | <a href="#" onClick="$('#tab1_chart4').show(); $('#tab1_chart2').hide(); $('#tab1_chart3').hide(); $('#tab1_chart1').hide(); return false;"><?php echo t("last_12_months", "last 12 months"); ?></a><br/><br/>

				<div id="tab1_chart1" style="display:none;">
					<?php

					/* last 24 hours chart */
					$tracker = 24;
					$last7Days = array();
					while($tracker >= 0)
					{
						$date = date("Y-m-d H:i:s", strtotime("-".$tracker." hour"));
						$last7Days[$date] = 0;
						$tracker--;
					}
					
					$db = Database::getDatabase(true);

					$minY = 0;
					$maxY = 10;
					foreach($last7Days AS $k=>$total)
					{
						$totalUrls = $db->getValue("SELECT COUNT(id) AS total FROM stats WHERE MID(dt, 1, 13) = '".substr($k, 0, 13)."' AND page_title = '".$file->id."'");
						$last7Days[$k] = (int)$totalUrls;
						if($totalUrls > $maxY)
						{
							$maxY = $totalUrls;
						}
					}

					$strXML  = "";
					$strXML .= "<graph yAxisMinValue='".$minY."' yAxisMaxValue='".$maxY."' canvasBorderThickness='1' showValues='0' canvasBgColor='dddddd' divlinecolor='ffffff' canvasBorderColor='cccccc' rotateNames='0' animation='1' xAxisName='".t("hour")."' yAxisName='".t("visits")."' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='12'>";
					foreach($last7Days AS $k=>$total)
					{
						$position = rand(0, COUNT($colours)-1);
						$strXML .= "<set name='".dater($k, "H")."' value='".(string)$total."' color='".$colours[$position]."'/>";
					}
					$strXML .= "</graph>";

					echo renderChartHTML("js/fusionCharts/Charts/FCF_Column2D.swf?noCache=".microtime(true), "", $strXML, "hourChart", 700, 300);
					?>
					
					<?php
					/* total visits figure */
					$totalVisits = 0;
					foreach($last7Days AS $k=>$total)
					{
						$totalVisits = $totalVisits + $total;
					}
					?>
					
					<br/>
					
					<div class="dataTableWrapper">
						<table class="dataTable">
							<thead>
								<tr>
									<th scope="col"><?php echo t("date"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$last7Days = array_reverse($last7Days, true);
								foreach($last7Days AS $k=>$total)
								{
									echo "<tr>";
									echo "<td>".dater($k, "H:00")."</td>";
									echo "<td class=\"figures\">".$total."</td>";
									echo "<td class=\"figures\">".number_format(($total/$totalVisits)*100, 1)."%</td>";
									echo "</tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div id="tab1_chart2" style="display:none;">
					<?php

					/* last 7 days chart */
					$tracker = 7;
					$last7Days = array();
					while($tracker >= 0)
					{
						$date = date("Y-m-d", strtotime("-".$tracker." day"));
						$last7Days[$date] = 0;
						$tracker--;
					}
					
					$db = Database::getDatabase(true);

					$minY = 0;
					$maxY = 10;
					foreach($last7Days AS $k=>$total)
					{
						$totalUrls = $db->getValue("SELECT COUNT(id) AS total FROM stats WHERE MID(dt, 1, 10) = '".$k."' AND page_title = '".$file->id."'");
						$last7Days[$k] = (int)$totalUrls;
						if($totalUrls > $maxY)
						{
							$maxY = $totalUrls;
						}
					}

					$strXML  = "";
					$strXML .= "<graph yAxisMinValue='".$minY."' yAxisMaxValue='".$maxY."' canvasBorderThickness='1' showValues='0' canvasBgColor='dddddd' divlinecolor='ffffff' canvasBorderColor='cccccc' rotateNames='0' animation='1' xAxisName='".t("day")."' yAxisName='".t("visits")."' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='12'>";
					foreach($last7Days AS $k=>$total)
					{
						$position = rand(0, COUNT($colours)-1);
						$strXML .= "<set name='".dater($k, "jS")."' value='".$total."' color='".$colours[$position]."'/>";
					}
					$strXML .= "</graph>";

					echo renderChartHTML("js/fusionCharts/Charts/FCF_Column2D.swf?noCache=".microtime(true), "", $strXML, "weekChart", 700, 300);
					?>
					
					<?php
					/* total visits figure */
					$totalVisits = 0;
					foreach($last7Days AS $k=>$total)
					{
						$totalVisits = $totalVisits + $total;
					}
					?>
					
					<br/>
					
					<div class="dataTableWrapper">
						<table class="dataTable">
							<thead>
								<tr>
									<th scope="col"><?php echo t("date", "date"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$last7Days = array_reverse($last7Days, true);
								foreach($last7Days AS $k=>$total)
								{
									echo "<tr>";
									echo "<td>".dater($k, SITE_CONFIG_DATE_FORMAT)."</td>";
									echo "<td class=\"figures\">".$total."</td>";
									echo "<td class=\"figures\">".number_format(($total/$totalVisits)*100, 1)."%</td>";
									echo "</tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div id="tab1_chart3" style="display:none;">
					<?php

					/* last 30 days chart */
					$tracker = 30;
					$last7Days = array();
					while($tracker >= 0)
					{
						$date = date("Y-m-d", strtotime("-".$tracker." day"));
						$last7Days[$date] = 0;
						$tracker--;
					}
					
					$db = Database::getDatabase(true);

					$minY = 0;
					$maxY = 10;
					foreach($last7Days AS $k=>$total)
					{
						$totalUrls = $db->getValue("SELECT COUNT(id) AS total FROM stats WHERE MID(dt, 1, 10) = '".$k."' AND page_title = '".$file->id."'");
						$last7Days[$k] = (int)$totalUrls;
						if($totalUrls > $maxY)
						{
							$maxY = $totalUrls;
						}
					}

					$strXML  = "";
					$strXML .= "<graph yAxisMinValue='".$minY."' yAxisMaxValue='".$maxY."' canvasBorderThickness='1' showValues='0' canvasBgColor='dddddd' divlinecolor='ffffff' canvasBorderColor='cccccc' rotateNames='0' animation='1' xAxisName='".t("day")."' yAxisName='".t("visits")."' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='12'>";
					foreach($last7Days AS $k=>$total)
					{
						$position = rand(0, COUNT($colours)-1);
						$strXML .= "<set name='".dater($k, "j")."' value='".$total."' color='".$colours[$position]."'/>";
					}
					$strXML .= "</graph>";

					echo renderChartHTML("js/fusionCharts/Charts/FCF_Column2D.swf?noCache=".microtime(true), "", $strXML, "monthChart", 700, 300);
					?>
					
					<?php
					/* total visits figure */
					$totalVisits = 0;
					foreach($last7Days AS $k=>$total)
					{
						$totalVisits = $totalVisits + $total;
					}
					?>
					
					<br/>
					
					<div class="dataTableWrapper">
						<table class="dataTable">
							<thead>
								<tr>
									<th scope="col"><?php echo t("date"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$last7Days = array_reverse($last7Days, true);
								foreach($last7Days AS $k=>$total)
								{
									echo "<tr>";
									echo "<td>".dater($k, SITE_CONFIG_DATE_FORMAT)."</td>";
									echo "<td class=\"figures\">".$total."</td>";
									echo "<td class=\"figures\">".number_format(($total/$totalVisits)*100, 1)."%</td>";
									echo "</tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div id="tab1_chart4" style="display:none;">
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
					
					$db = Database::getDatabase(true);

					$minY = 0;
					$maxY = 10;
					foreach($last7Days AS $k=>$total)
					{
						$totalUrls = $db->getValue("SELECT COUNT(id) AS total FROM stats WHERE MID(dt, 1, 7) = '".$k."' AND page_title = '".$file->id."'");
						$last7Days[$k] = (int)$totalUrls;
						if($totalUrls > $maxY)
						{
							$maxY = $totalUrls;
						}
					}

					$strXML  = "";
					$strXML .= "<graph yAxisMinValue='".$minY."' yAxisMaxValue='".$maxY."' canvasBorderThickness='1' showValues='0' canvasBgColor='dddddd' divlinecolor='ffffff' canvasBorderColor='cccccc' rotateNames='0' animation='1' xAxisName='".t("month")."' yAxisName='".t("visits")."' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='12'>";
					foreach($last7Days AS $k=>$total)
					{
						$position = rand(0, COUNT($colours)-1);
						$strXML .= "<set name='".dater($k, "M y")."' value='".$total."' color='".$colours[$position]."'/>";
					}
					$strXML .= "</graph>";
					
					echo renderChartHTML("js/fusionCharts/Charts/FCF_Column2D.swf?noCache=".microtime(true), "", $strXML, "yearChart", 700, 300);
					?>
					
					<?php
					/* total visits figure */
					$totalVisits = 0;
					foreach($last7Days AS $k=>$total)
					{
						$totalVisits = $totalVisits + $total;
					}
					?>
					
					<br/>
					
					<div class="dataTableWrapper">
						<table class="dataTable">
							<thead>
								<tr>
									<th scope="col"><?php echo t("date"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$last7Days = array_reverse($last7Days, true);
								foreach($last7Days AS $k=>$total)
								{
									echo "<tr>";
									echo "<td>".dater($k, "M y")."</td>";
									echo "<td class=\"figures\">".$total."</td>";
									echo "<td class=\"figures\">".number_format(($total/$totalVisits)*100, 1)."%</td>";
									echo "</tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
			</div>
			
			<div>
				<!-- TAB 2 -->
				<?php

				/* pie chart */
				$dataForPie = $db->getRows("SELECT country, COUNT(id) AS total FROM stats WHERE page_title = '".$file->id."' GROUP BY country ORDER BY total DESC");

				$strXML  = "";
				$strXML .= "<graph animation='1' showValues='0' showNames='1' pieRadius='95' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='10'>";
				foreach($dataForPie AS $dataRow)
				{
					$position = rand(0, COUNT($colours)-1);
					$strXML .= "<set name='".t(UCWords(($dataRow['country'])))."' value='".$dataRow['total']."' color='".$colours[$position]."'/>";
				}
				$strXML .= "</graph>";

				echo renderChartHTML("js/fusionCharts/Charts/FCF_Pie2D.swf", "", $strXML, "statusPie", 500, 250);

				?>
				
				<?php
				/* total visits figure */
				$totalVisits = 0;
				foreach($dataForPie AS $dataRow)
				{
					$totalVisits = $totalVisits + $dataRow['total'];
				}
				?>
				
				<br/>
				
				<div class="dataTableWrapper">
					<table class="dataTable">
						<thead>
							<tr>
								<th scope="col"><?php echo t("country", "country"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($dataForPie AS $dataRow)
							{
								$countryCode = $dataRow['country']?$dataRow['country']:"unknown";
								$flagPath = SITE_IMAGE_PATH."/stats/flags/".strtolower($countryCode).".png";
								echo "<tr>";
								echo "<td><img src=\"".$flagPath."\" width='16' height='11' alt=\"".t($countryCode, $countryCode)."\">&nbsp;&nbsp;".t($countryCode, $countryCode)."</td>";
								echo "<td class=\"figures\">".$dataRow['total']."</td>";
								echo "<td class=\"figures\">".number_format(($dataRow['total']/$totalVisits)*100, 1)."%</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
				
			</div>
			
			<div>
				<!-- TAB 3 -->
				<?php
				/* pie chart  */
				$dataForPie = $db->getRows("SELECT base_url, COUNT(id) AS total FROM stats WHERE page_title = '".$file->id."' GROUP BY base_url ORDER BY total DESC LIMIT 20");

				$strXML  = "";
				$strXML .= "<graph animation='1' showValues='0' showNames='1' pieRadius='95' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='10'>";
				foreach($dataForPie AS $dataRow)
				{
					$position = rand(0, COUNT($colours)-1);
					$strXML .= "<set name='".(($dataRow['base_url']))."' value='".$dataRow['total']."' color='".$colours[$position]."'/>";
				}
				$strXML .= "</graph>";

				echo renderChartHTML("js/fusionCharts/Charts/FCF_Pie2D.swf", "", $strXML, "statusPie", 500, 250);

				?>
				
				<?php
				/* total visits figure */
				$totalVisits = 0;
				foreach($dataForPie AS $dataRow)
				{
					$totalVisits = $totalVisits + $dataRow['total'];
				}
				?>
				
				<br/>
				
				<div class="dataTableWrapper">
					<table class="dataTable">
						<thead>
							<tr>
								<th scope="col"><?php echo t("site", "site"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($dataForPie AS $dataRow)
							{
								$baseUrl = $dataRow['base_url']?$dataRow['base_url']:"direct";
								echo "<tr>";
								echo "<td>";
								if($dataRow['base_url'])
								{
									echo "<a href='http://".$baseUrl."' target='_blank'>".$baseUrl."</a>";
								}
								else
								{
									echo $baseUrl;
								}
								echo "</td>";
								echo "<td class=\"figures\">".$dataRow['total']."</td>";
								echo "<td class=\"figures\">".number_format(($dataRow['total']/$totalVisits)*100, 1)."%</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
				
			</div>
			
			<div>
				<!-- TAB 4 -->
				<?php
				/* pie chart  */
				$dataForPie = $db->getRows("SELECT browser_family, COUNT(id) AS total FROM stats WHERE page_title = '".$file->id."' GROUP BY browser_family ORDER BY total DESC LIMIT 20");

				$strXML  = "";
				$strXML .= "<graph animation='1' showValues='0' showNames='1' pieRadius='95' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='10'>";
				foreach($dataForPie AS $dataRow)
				{
					$position = rand(0, COUNT($colours)-1);
					$strXML .= "<set name='".(($dataRow['browser_family']))."' value='".$dataRow['total']."' color='".$colours[$position]."'/>";
				}
				$strXML .= "</graph>";

				echo renderChartHTML("js/fusionCharts/Charts/FCF_Pie2D.swf", "", $strXML, "statusPie", 500, 250);

				?>
				
				<?php
				/* total visits figure */
				$totalVisits = 0;
				foreach($dataForPie AS $dataRow)
				{
					$totalVisits = $totalVisits + $dataRow['total'];
				}
				?>
				
				<br/>
				
				<div class="dataTableWrapper">
					<table class="dataTable">
						<thead>
							<tr>
								<th scope="col"><?php echo t("browser", "browser"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($dataForPie AS $dataRow)
							{
								$browser = $dataRow['browser_family']?$dataRow['browser_family']:"unknown";
								$iconPath = SITE_IMAGE_PATH."/stats/browsers/".strtolower($browser).".png";
								echo "<tr>";
								echo "<td><img src=\"".$iconPath."\" width='14' height='14' alt=\"".$browser."\" style=\"float:left;\">&nbsp;&nbsp;".UCWords($browser)."</td>";
								echo "<td class=\"figures\">".$dataRow['total']."</td>";
								echo "<td class=\"figures\">".number_format(($dataRow['total']/$totalVisits)*100, 1)."%</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
				
			</div>
			
			<div>
				<!-- TAB 5 -->
				<?php
				/* pie chart  */
				$dataForPie = $db->getRows("SELECT os, COUNT(id) AS total FROM stats WHERE page_title = '".$file->id."' GROUP BY os ORDER BY total DESC LIMIT 20");

				$strXML  = "";
				$strXML .= "<graph animation='1' showValues='0' showNames='1' pieRadius='95' decimalPrecision='0' formatNumberScale='0' baseFont='Arial' baseFontSize='10'>";
				foreach($dataForPie AS $dataRow)
				{
					$position = rand(0, COUNT($colours)-1);
					$strXML .= "<set name='".(($dataRow['os']))."' value='".$dataRow['total']."' color='".$colours[$position]."'/>";
				}
				$strXML .= "</graph>";

				echo renderChartHTML("js/fusionCharts/Charts/FCF_Pie2D.swf", "", $strXML, "statusPie", 500, 250);

				?>
				
				<?php
				/* total visits figure */
				$totalVisits = 0;
				foreach($dataForPie AS $dataRow)
				{
					$totalVisits = $totalVisits + $dataRow['total'];
				}
				?>
				
				<br/>
				
				<div class="dataTableWrapper">
					<table class="dataTable">
						<thead>
							<tr>
								<th scope="col"><?php echo t("operating_system", "operating system"); ?></th>
									<th scope="col" class="figures"><?php echo t("total_visits", "total visits"); ?></th>
									<th scope="col" class="figures"><?php echo t("percentage", "percentage"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($dataForPie AS $dataRow)
							{
								$os = $dataRow['os']?$dataRow['os']:"unknown";
								$iconPath = SITE_IMAGE_PATH."/stats/os/".strtolower($os).".png";
								echo "<tr>";
								echo "<td><img src=\"".$iconPath."\" width='14' height='14' alt=\"".$os."\" style=\"float:left;\">&nbsp;&nbsp;".UCWords($os)."</td>";
								echo "<td class=\"figures\">".$dataRow['total']."</td>";
								echo "<td class=\"figures\">".number_format(($dataRow['total']/$totalVisits)*100, 1)."%</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
				
			</div>
			
		</div>
	</div>
</div>

<script>
(function() {
    var tabView = new YAHOO.widget.TabView('demo');
	$('#tab1_chart1').show();
})();
</script>

<?php
	require_once('_footer.php');
?>