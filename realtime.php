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
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
<p class="trigger"><a>Click to toggle options</a></p>
<div class="toggle_container">
<div class="block">
<div class="col-sm-6 col-sm-offset-3">
<form action="" method="GET">
<div id="date-group" class="form-group">
<p>
<label for="sdate">Start Date:</label>
<input type="text" id="startdatepicker" name="startdatepicker" value="<?php echo $startdate?>"> 
</p>
<p>
<label for="edate">End Date:</label>
<input type="text" id="enddatepicker" name="enddatepicker"value="<?php echo $enddate?>"></p>
</p>
</div>
  <div id="interval class="form-group">
  <label for="interval">Time Interval</label>
    <fieldset>
    <input type="radio" name="radio-1" value="interval5" checked="checked">
      <label for="interval"> 5 minutes </label>
      <br>
      <input type="radio" name="radio-1" value="interval30">
      <label for="interval"> 30 minutes</label>
      <br>
      <input type="radio" name="radio-1" value="intervalhour">
      <label for="interval"> Hour </label>
      <br>
      <input type="radio" name="radio-1" value="intervalday">
      <label for="interval"> Day </label>
      <br>
      <input type="radio" name="radio-1" value="intervalmonth">
      <label for="interval"> 30 days </label>
      <br>
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
