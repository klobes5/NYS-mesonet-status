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
  //Sets the time interval based on radio box 
  if (isset($_GET['radio-1'])){
    $interval = urldecode ($_GET['radio-1']);
    //echo "\nInterval is: ".$interval;
    switch ($interval){
      case "interval5":
        $myInterval = 300; //seconds in five minutes
        $max = 1; //Max possible reports
        break;
      case "interval30":
        $myInterval = 1800; //seconds in 30 minutes
        $max = 6; 
        break;
      case "intervalhour":
        $myInterval = 3600; //seconds in an hour
        $max = 12; 
        break;
      case "intervalday":
        $myInterval = 86400; //seconds in a day
        $max = 288; 
        break;
      case "intervalmonth":
        $myInterval = 2592000; //seconds in 30 days
        $max = 8640;
        break;
    }
    //echo "\nValue of myInterval is: ".$myInterval;
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
   // $start = date("Y-m-d",str_replace('/','-', $startdate));
    //$end = date("Y-m-d",str_replace('/','-', $enddate));
    //$start = date("Y-m-d H:i:s", strtotime($startdate));
    //$end = date("Y-m-d H:i:s", strtotime($enddate));
    $start = date("Y-m-d", strtotime($startdate));
    $end = date("Y-m-d 23:59:59", strtotime($enddate));
    if(isset($_GET['startdatepicker']) AND isset($_GET['enddatepicker']) AND isset($_GET['radio-1'])){
        $sql = "select stationId, reportTime, SUM(missed) as sum from SiteStatusHistory_dev.siteMisses WHERE reportTime BETWEEN '$start' and '$end' Group BY stationId, UNIX_TIMESTAMP(reportTime) DIV $myInterval ORDER BY stationId ASC";
       // echo $sql;
    } else{
      $sql = "select stationId, reportTime, SUM(missed) as sum from SiteStatusHistory_dev.siteMisses WHERE reportTime BETWEEN '2016-05-02 00:00:00' and '2016-06-10 23:59:00' Group BY stationId, UNIX_TIMESTAMP(reportTime) DIV 3600 ORDER BY stationId ASC";
     // echo $sql;
    }

    $stmt = $dbh->query($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = [];    

    while($row =$stmt->fetch())
    {    
         $time = strtotime($row['reportTime']) * 1000;
        //$time = strtotime($row['lastReportTime']);
         $index = array_search($row['stationId'], $sites);
         $frequency = $row['sum'];
         
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
<script src="./process.js"></script>
<script>
var sites = <?php echo json_encode($sites); ?>;
var data = <?php echo json_encode($data); ?>;
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<p class="trigger"><a>Click here For Options</a></p>
<div class="toggle_container">
<div class="block">
<div class="col-sm-6 col-sm-offset-3">
<form action="stationstatus.php" method="GET">
<div id="date-group" class="form-group">
<label for="sdate">Start Date:</label>
<input type="text" id="startdatepicker" name="startdatepicker" value="<?php echo $startdate?>"> 
<label for="edate">End Date:</label>
<input type="text" id="enddatepicker" name="enddatepicker"value="<?php echo $enddate?>"></p>
</div>
  <div id="interval class="form-group">
  <label for="interval">Time Interval</label>
    <fieldset>
      <label for="interval"> 5 minutes: </label>
      <input type="radio" name="radio-1" value="interval5"><br>
      <label for="interval"> 30 minutes: </label>
      <input type="radio" name="radio-1" value="interval30"><br>
      <label for="interval"> Hour: </label>
      <input type="radio" name="radio-1" value="intervalhour"><br>
      <label for="interval"> Day: </label>
      <input type="radio" name="radio-1" value="intervalday"><br>
      <label for="interval"> 30 days: </label>
      <input type="radio" name="radio-1" value="intervalmonth"><br>
    </fieldset>
  </div>
  <button type="submit" class="btn btn-success">Submit <span class="fa fa-arrow-right"></span></button>

</form>
</div>
</div>
</div>
<span></span>
<div id="container"></div>
</body>
</html>
