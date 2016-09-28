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

  //below is temporary for testing
  $startDate = strtotime('2016-09-01 00:00:00');
  //--------------

  //holds data to be passed into php for resubmit
  $data = array();

  //start and end dates from form
  if (isset($_GET['startdatepicker'])){
    $startdate = urldecode ($_GET['startdatepicker']);
    echo "chosen startdate is: ".$startdate;
  }
  if (isset($_GET['startdatepicker'])){
    $enddate = urldecode ($_GET['enddatepicker']);
    echo "\nchosen enddate is: ".$enddate;
  }
  if (isset($_GET['radio-1'])){
    $interval = urldecode ($_GET['radio-1']);
    echo "\nInterval is: ".$interval;
  }
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
WHERE lastReportTime BETWEEN '2016-09-14 00:00:00' and '2016-09-14 23:59:00'
Group BY stationId, UNIX_TIMESTAMP(lastReportTime) DIV 3600 ORDER BY stationId ASC";

    $stmt = $dbh->query($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = [];    

    while($row =$stmt->fetch())
    {    
         $time = strtotime($row['lastReportTime']) * 1000;
        //$time = strtotime($row['lastReportTime']);
         $index = array_search($row['stationId'], $sites);
         $frequency = $row['count'];
         
         //push the information to data fit for HighCharts
         array_push($data, [$time, $index, $frequency]);
         
    }

 ?>
<head>
<title>Heatmap</title>
<style>
#container {
  width: 100%;
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
<script src="./process.js"></script>
<script>
var sites = <?php echo json_encode($sites); ?>;
var data = <?php echo json_encode($data); ?>;
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/~klobiondo/jquery-ui-1.12.1.custom/external/jquery/jquery.js">
<div class="col-sm-6 col-sm-offset-3">
<form action="stationstatus.php" method="GET">
<div id="date-group" class="form-group">
<label for="sdate">Start Date:</label>
<input type="text" id="startdatepicker" name="startdatepicker"> 
<label for="edate">End Date:</label>
<input type="text" id="enddatepicker" name="enddatepicker"></p>
</div>

  <div id="interval class="form-group">
  <label for="interval">Time Interval</label>
    <fieldset>
      <label for="interval"> 5 minutes: </label>
      <input type="radio" name="radio-1" value="interval5">
      <label for="interval"> 30 minutes: </label>
      <input type="radio" name="radio-1" value="interval30">
      <label for="interval"> Hour: </label>
      <input type="radio" name="radio-1" value="intervalhour">
      <label for="interval"> Day: </label>
      <input type="radio" name="radio-1" value="intervalday">
      <label for="interval"> 30 days: </label>
      <input type="radio" name="radio-1" value="intervalmonth">
    </fieldset>
  </div>
  <button type="submit" class="btn btn-success">Submit <span class="fa fa-arrow-right"></span></button>

</form>
</div>
<span></span>
<div id="container"></div>
</body>
</html>
