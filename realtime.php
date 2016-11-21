<html>
<?php
 require("/mnt/remote/www/test/common/config/db/constants.php"); 
  $host=DB_WEB_HOSTNAME;
  $user= DB_WEB_USERNAME;
  $password=DB_WEB_PASSWORD;
  $dbname='SiteStatusHistory_dev';
  $dsn = "mysql:host=$host;dbname=$dbname"; 
  $station_table_stack=array();
  $dbh=null;
  //Below are intervals in UNIX Time for SQL query
  //below is temporary for testing
  //$startDate = strtotime('2016-09-01 00:00:00');
  //--------------
  //holds data to be passed into php for resubmit
  $data = array();
  //start and end dates from form
  if (isset($_GET['startdatepicker'])){
    $startdate = urldecode ($_GET['startdatepicker']);
    //echo "chosen startdate is: ".$startdate;
  }
  if (isset($_GET['enddatepicker'])){
    $enddate = urldecode ($_GET['enddatepicker']);
    //echo "\nchosen enddate is: ".$enddate;
  }

    $myInterval = 300; //seconds in five minutes
    $max = 1; //Max possible reports
  
  try{
    $dbh= new PDO($dsn, $user, $password);
    if($dbh)
    {
     // echo "Connected to the <strong>$dbname</strong> database successfully!";
        }
  }
  catch (PDOException $e){
    echo $e->getMessage();
  }

    //fetch table rows from mysql db
    $sql = "select distinct stationId from SiteStatusHistory_dev.StationReport_tbl where stationId not like 'BARN' AND stationId not LIKE 'EXPASRC2' AND stationId not like 'NYSFAIR' and stationId not like 'TEST3000' and stationId not like 'WFMBSNOW' order by stationId DESC";
    $stmt = $dbh->query($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $sarray = [];
    while($row =$stmt->fetch())
    {
        $sites[] = $row['stationId'];
        //echo $row['stid'];
    }
     //array_push($sites, $sites);

 ?>
<head>
<title>Heatmap</title>
<style>
#container {
  width: 100%;
}
.highcharts-container{
/** Rules it should follow **/
}
<style is="custom-style" include="iron-flex iron-flex-alignment">
</style>
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/highcharts-more.js"></script>
<script src="http://code.highcharts.com/modules/heatmap.js"></script>
<script src="http://momentjs.com/downloads/moment.min.js"></script>
<script src="./realtime.js"></script>
<script>
var sites = <?php echo json_encode($sites); ?>;
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
<span></span>
<div id="container"></div>
</body>
</html>
