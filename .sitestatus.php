<!DOCTYPE html>
<html>
<?php
$sdate = (array_key_exists('sdate', $_GET) && preg_match('/^[0-9]{4}[0-9]{2}[0-9]{2}$/', $_GET['sdate'])) ? $_GET['sdate'] : strftime('%Y%m%d', time() - (86400 * 90));
$edate = (array_key_exists('edate', $_GET) && preg_match('/^[0-9]{4}[0-9]{2}[0-9]{2}$/', $_GET['edate'])) ? $_GET['edate'] : strftime('%Y%m%d', time());
require($_SERVER['DOCUMENT_ROOT'] . "/common/config/db/constants.php");
$pdo = new PDO('mysql:host='.DB_READONLY_HOSTNAME.";dbname=SiteStatusHistory_dev", DB_READONLY_USERNAME, DB_READONLY_PASSWORD);

$dbsdate = sprintf("%s-%s-%s", substr($sdate, 0, 4), substr($sdate, 4, 2), substr($sdate, 6, 2));
$dbedate = sprintf("%s-%s-%s", substr($edate, 0, 4), substr($edate, 4, 2), substr($edate, 6, 2));

$sites = [];
$stmt = $pdo->prepare('SELECT DISTINCT stationId FROM StationReport_tbl WHERE lastReportTime >= :sdate AND lastReportTime <= :edate ORDER BY stationId DESC');
$stmt->execute(array(':sdate' => $sdate, ':edate' => $edate));
foreach ($stmt as $row)
{
  $sites[] = $row['stationId'];
}

$data = [];
$stmt = $pdo->prepare('SELECT stationId, lastReportTime FROM StationReport_tbl WHERE lastReportTime >= :sdate AND lastReportTime <= :edate ORDER BY stationId DESC, lastReportTime');
$stmt->execute(array(':sdate' => $sdate, ':edate' => $edate));

/*****Frequency will get incremented through comparison with previous row****/
$frequency = 0;
foreach ($stmt as $row)
{
  $data[] = [strtotime($row['lastReportTime']) * 1000, array_search($row['stationId'], $sites), $frequency * 1];
}
$sitestatusdb = new PDO('mysql:host='.DB_READONLY_HOSTNAME.";dbname=SiteStatusHistory_dev", DB_READONLY_USERNAME, DB_READONLY_PASSWORD);
$stmt = $sitestatusdb->prepare('SELECT stationId, lastReportTime FROM StationReport_tbl ORDER BY stationId');
$stmt->execute();
$qualparm = [];
$x = $data[count($data) - 1][0];
foreach ($stmt as $row)
{
  $index = array_search($row['stationId'], $sites);
  if ($index !== FALSE)
    $qualparm[] = array(
      'x' => $x,
      'y' => $index,
      'color' => ($frequency == "0") ? "#00FF00" : "#FF0000"
    );
}

?>
<head>
<title>Heatmap</title>
<style>
#container {
  width: 100%;
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/highcharts-more.js"></script>
<script src="http://code.highcharts.com/modules/heatmap.js"></script>
<script src="http://momentjs.com/downloads/moment.min.js"></script>
<script>
var sites = <?php echo json_encode($sites); ?>;
var data = <?php echo json_encode($data); ?>;
var qualparm = <?php echo json_encode($qualparm); ?>;
//    /**
//     * This plugin extends Highcharts in two ways:
//     * - Use HTML5 canvas instead of SVG for rendering of the heatmap squares. Canvas
//     *   outperforms SVG when it comes to thousands of single shapes.
//     * - Add a K-D-tree to find the nearest point on mouse move. Since we no longer have SVG shapes
//     *   to capture mouseovers, we need another way of detecting hover points for the tooltip.
//     */
//    (function (H) {
//        var Series = H.Series,
//            each = H.each;
//
//        /**
//         * Create a hidden canvas to draw the graph on. The contents is later copied over
//         * to an SVG image element.
//         */
//        Series.prototype.getContext = function () {
//            if (!this.canvas) {
//                this.canvas = document.createElement('canvas');
//                this.canvas.setAttribute('width', this.chart.chartWidth);
//                this.canvas.setAttribute('height', this.chart.chartHeight);
//                this.image = this.chart.renderer.image('', 0, 0, this.chart.chartWidth, this.chart.chartHeight).add(this.group);
//                this.ctx = this.canvas.getContext('2d');
//            }
//            return this.ctx;
//        };
//
//        /**
//         * Draw the canvas image inside an SVG image
//         */
//        Series.prototype.canvasToSVG = function () {
//            this.image.attr({ href: this.canvas.toDataURL('image/png') });
//        };
//
//        /**
//         * Wrap the drawPoints method to draw the points in canvas instead of the slower SVG,
//         * that requires one shape each point.
//         */
//        H.wrap(H.seriesTypes.heatmap.prototype, 'drawPoints', function () {
//
//            var ctx = this.getContext();
//
//            if (ctx) {
//
//                // draw the columns
//                each(this.points, function (point) {
//                    var plotY = point.plotY,
//                        shapeArgs;
//
//                    if (plotY !== undefined && !isNaN(plotY) && point.y !== null) {
//                        shapeArgs = point.shapeArgs;
//
//                        ctx.fillStyle = point.pointAttr[''].fill;
//                        ctx.fillRect(shapeArgs.x, shapeArgs.y, shapeArgs.width, shapeArgs.height);
//                    }
//                });
//
//                this.canvasToSVG();
//
//            } else {
//                this.chart.showLoading('Your browser doesn\'t support HTML5 canvas, <br>please use a modern browser');
//
//                // Uncomment this to provide low-level (slow) support in oldIE. It will cause script errors on
//                // charts with more than a few thousand points.
//                // arguments[0].call(this);
//            }
//        });
//        H.seriesTypes.heatmap.prototype.directTouch = false; // Use k-d-tree
//    }(Highcharts));
$(document).ready(function () {
  var pixels_per_site = 15;
  var margins = 35;
  var height = pixels_per_site * sites.length + margins;
  $('#container').height(height);
  console.log(sites.length);
  $('#container').highcharts({
    chart: {
      type: 'heatmap',
      marginTop: 0,
      marginBottom: 35,
      plotBorderWidth: 1
    },
    credits: {enabled: false},
    title: { text: '' },
    xAxis: {
      gridLineWidth: 1,
      type: 'datetime'
    },
    yAxis: {
      categories: sites,
      title: null
    },
    colorAxis: {
      min: 0,
      max: 10,
      stops: [
        [0, '#00AA00'],
        [.3, '#00FF00'],
        [.6, '#FFFF00'],
        [1, '#FF0000'],
      ],
      minColor: '#00FF00',
      maxColor: '#FF0000'
    },
    legend: {
      align: 'right',
      layout: 'vertical',
      margin: 0,
      verticalAlign: 'top',
      y: 25,
      symbolHeight: 280
    },
    plotOptions: {
      heatmap: {
        colsize: 86400000,
        turboThreshold: 0
      }
    },
    tooltip: {
      formatter: function () {
        return '<b>' + this.series.yAxis.categories[this.point.y] + ' ' + Highcharts.dateFormat('%Y-%m-%d', this.point.x) + '</b>: ' + this.point.value;
      }
    },
    
    series: [{
      borderWidth: 0.5,
      data: data,
      dataLabels: {
        enabled: false
      },
      events: {
        click: function (e) {
          var m = moment.utc(e.point.x);
          var start = m.clone().subtract(12, 'hours').format('YYYYMMDD[T]HHmm');
          var end = m.clone().add(36, 'hours').format('YYYYMMDD[T]HHmm');
          var site = sites[e.point.y];
          var url = 'http://operations.nysmesonet.org/~nbain/weird_precip/?stationId='+site+'&sdate='+start+'&edate='+end;
          window.open(url);
        }
      }
    },
    {
      animation: false,
      data: qualparm,
      enableMouseTracking: false,
      name: 'Public?',
      type: 'scatter'
    }]
  });
});
</script>
</head>
<body>
This is a chart of bucket loss per lastReportTime. This is calculated by looking at the change in the bucket every minute. If there is a negative change compared to the previous minute, the volume lost is added to the total. If there is a positive change (i.e. it "SiteStatusHistory_dev.StationReport_tbled", whether real or false accumulation), then nothing is added. <b>Units are mm.</b> Click on a square to go to a precip graph for that period.<br>
<form action="" method="GET">
<label for="sdate">Starting date (begins 20150811): </label><input type="text" name="sdate" id="sdate" value="<?=$sdate?>"></input>
<label for="edate">Ending date (ends tolastReportTime): </label><input type="text" name="edate" id="edate" value="<?=$edate?>"></input>
<input type="submit" value="Go!">
</form>
<br>
<div id="container"></div>
</body>
</html>
