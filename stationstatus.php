<html>
<head>
<title>Site Status Report History</title>
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
   <script src="http://code.highcharts.com/highcharts.js"></script>    
   <script src="http://code.highcharts.com/highcharts-more.js"></script>    
   <script src="http://code.highcharts.com/modules/heatmap.js"></script>  
</head>
<body>
<div id="container"></div>
<?php
  require("/mnt/remote/www/test/common/config/db/constants.php"); 
  $host=DB_WEB_HOSTNAME;
  $user= DB_WEB_USERNAME;
  $password=DB_WEB_PASSWORD;
  $dbname='SiteStatusHistory_dev';
  $dsn = "mysql:host=$host;dbname=$dbname"; 
  $station_table_stack=array();
  $dbh=null;
  try{
    $dbh= new PDO($dsn, $user, $password);
    if($dbh)
    {
      echo "Connected to the <strong>$dbname</strong> database successfully!";
        }
  }
  catch (PDOException $e){
    echo $e->getMessage();
  }

    //fetch table rows from mysql db
    $sql = "select distinct stationId from SiteStatusHistory_dev.StationReport_tbl order by stationId DESC";
    $stmt = $dbh->query($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $sarray = [];
    while($row =$stmt->fetch())
    {
        $sites[] = $row['stationId'];
        //echo $row['stid'];
    }

   //$sql = "select stationid, lastReportTime from SiteStatusHistory_dev.StationReport_tbl WHERE lastReportTime BETWEEN '2016-09-01 00:00:00' and '2016-09-07 00:00:00' ORDER BY stationid, lastReportTime ASC";
    $sql = "select stationId, lastReportTime, count(*) as count
from SiteStatusHistory_dev.StationReport_tbl 
WHERE lastReportTime BETWEEN '2016-09-13 00:00:00' and '2016-09-13 23:00:00'
Group BY stationId, UNIX_TIMESTAMP(lastReportTime) DIV 3600 ORDER BY stationId ASC";

    $stmt = $dbh->query($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = [];    

/*******initialize values for manipulation in loop that follows *******/ 
  //Start time is input from webform
  //below is temporary for testing
   $startDate = strtotime('2016-09-01 00:00:00');
   //prevDate is for comparisons
 
    while($row =$stmt->fetch())
    {    
         $time = strtotime($row['lastReportTime']) * 1000;
        //$time = strtotime($row['lastReportTime']);
         $index = array_search($row['stationId'], $sites);
         $frequency = $row['count'];
         
         //push the information to data fit for HighCharts
         array_push($data, [$time, $index, $frequency]);
         
    }


    /* $dbdata = [
      ['stationId' => 'BATA', 'time' => '2016-05-09 13:50:00', 'flag' => 1],
      ['stationId' => 'BELD', 'time' => '2016-05-09 13:50:00', 'flag' => 1],
      ['stationId' => 'BELL', 'time' => '2016-05-09 13:50:00', 'flag' => 0],
      ['stationId' => 'BERK', 'time' => '2016-05-09 13:50:00', 'flag' => 1],
      ['stationId' => 'BRAN', 'time' => '2016-05-09 13:50:00', 'flag' => 1],
      ['stationId' => 'BATA', 'time' => '2016-05-09 13:55:00', 'flag' => 1],
      ['stationId' => 'BELD', 'time' => '2016-05-09 13:55:00', 'flag' => 1],
      ['stationId' => 'BELL', 'time' => '2016-05-09 13:55:00', 'flag' => 0],
      ['stationId' => 'BERK', 'time' => '2016-05-09 13:55:00', 'flag' => 1],
      ['stationId' => 'BRAN', 'time' => '2016-05-09 13:55:00', 'flag' => 1],
      ['stationId' => 'BATA', 'time' => '2016-05-09 14:00:00', 'flag' => 0],
      ['stationId' => 'BELD', 'time' => '2016-05-09 14:00:00', 'flag' => 1],
      ['stationId' => 'BELL', 'time' => '2016-05-09 14:00:00', 'flag' => 1],
      ['stationId' => 'BERK', 'time' => '2016-05-09 14:00:00', 'flag' => 1],
      ['stationId' => 'BRAN', 'time' => '2016-05-09 14:00:00', 'flag' => 1]
    ];

    $data = [];
    foreach ($dbdata as $row) {
      $time = strtotime($row['time']) * 1000;
      $siteid = array_search($row['stationId'], $sarray);
      $value = $row['flag'];
      array_push($data, [$time, $siteid, $value]);
    } */
    

 ?>
<script language="JavaScript">
var sites = <?php echo json_encode($sites); ?>;
var data = <?php echo json_encode($data); ?>;

$(document).ready(function () {

   var pixels_per_site = 15;
   var margins = 35;
   var height = pixels_per_site * sites.length + margins;
    $('#container').height(height);
    console.log(sites.length);
    $('#container').highcharts({
        chart: {
            type: 'heatmap',
            marginTop: 40,
            marginBottom: 80,
            plotBorderWidth: 1,
      height: 1000
        },


        title: {
            text: 'Station Report'
        },

        xAxis: {
            gridLineWidth:1,
      type:'datetime'
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
        [6, '#00FF00'],
        [12, '#FFFF00'],
        [24, '#FF0000'],
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
            name: 'Site Status',
            borderWidth: 1,
            data: data,
            dataLabels: {
                enabled: true,
                color: '#000000'
            }
        }]

    });
});
</script>

</body>
</html>
