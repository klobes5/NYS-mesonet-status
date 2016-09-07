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
    $sql = "select distinct stid from metadb_prod.site order by stid";
    $stmt = $dbh->query($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
    $sarray = [];
    while($row =$stmt->fetch())
    {
        $sarray[] = $row['stid'];
    }
	$q='call getData()';
	$dbh->query($q);
	$sql = "select stationId,reportTime,flagdivya from filedata order by stationId";
    $stmt = $dbh->query($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = [];    
    while($row =$stmt->fetch())
    {
         $time = strtotime($row['reportTime']) * 1000;
      	  $siteid = array_search($row['stationId'], $sarray);
         $value = $row['flagdivya'];
         array_push($data, [$time, $siteid, $value]);
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
var sites = <?php echo json_encode($sarray); ?>;
var data = <?php echo json_encode($data); ?>;

$(function () {

    $('#container').highcharts({

        chart: {
            type: 'heatmap',
            marginTop: 40,
            marginBottom: 80,
            plotBorderWidth: 1,
			height: 1000
        },


        title: {
            text: 'Site Status'
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
            minColor: '#FF0000',
            maxColor: '#00FF00'
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
				colsize: 300000,
				turboThreshold: 0
			}
		},
        tooltip: {
		enabled: false
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
