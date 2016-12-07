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
try{
    $dbh= new PDO($dsn, $user, $password);
    if($dbh)
    {
     //echo "Connected to the <strong>$dbname</strong> database successfully!";
        }
  }
  catch (PDOException $e){
    echo $e->getMessage();
  }
    $sql = "select distinct stationId from SiteStatusHistory_dev.StationReport_tbl where stationId not like 'BARN' AND stationId not LIKE 'EXPASRC2' AND stationId not like 'NYSFAIR' and stationId not like 'TEST3000' and stationId not like 'WFMBSNOW' order by stationId DESC";
    $stmt = $dbh->query($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $sarray = [];
    while($row =$stmt->fetch())
    {
        $sites[] = $row['stationId'];
        //echo $row['stid'];
    }
//Get Data from the last 24 hours With some logic for custom Intervals and chart update functions
	//$sql = "(select stationId ,reportTime, SUM(missed) from siteMisses where reportTime BETWEEN  (NOW() - INTERVAL 1 DAY) and NOW() and reportTime > ? group BY stationId, UNIX_TIMESTAMP(reportTime) DIV ? ORDER BY stationId ASC), $lastUpdate, $myInterval";

//standard 5 minute interval with last 24 hours drawn
  $sql = "select stationId ,reportTime, SUM(missed) as sum from siteMisses where reportTime BETWEEN  (NOW() - INTERVAL 1 DAY) and NOW()  group BY stationId, UNIX_TIMESTAMP(reportTime) DIV 300 ORDER BY stationId ASC";
//$sql = "select stationId ,reportTime, SUM(missed) as sum from siteMisses where reportTime BETWEEN  '2016-06-02 00:00:00' and '2016-06-02 23:59:59' and reportTime > '2016-06-02 00:00:00' group BY stationId, UNIX_TIMESTAMP(reportTime) DIV 300 ORDER BY stationId ASC";
    $stmt = $dbh->query($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    //initialize data array
    $data = []; 

    //json header
    header('Content-Type: application/json');
    while($row =$stmt->fetch())
    {    
         $time = strtotime($row['reportTime']) * 1000;
        //$time = strtotime($row['lastReportTime']);
         $index = array_search($row['stationId'], $sites);
         $frequency = (int)$row['sum'];

         $object[] = array(
            "time" => $time,
            "index" => $index,
            "frequency" => $frequency 
          );
         //push the information to data fit for HighCharts
         // The json has the format of {
          //                             "point": { 
         //                                 "time": "datetime", 
           //                               "index": "index corresponding to station on y axis", 
           //                               "frequency": "number of misses"
         //                                 }
         //                             }
         //echo json_encode(array('point' => $object),JSON_PRETTY_PRINT);

    }
          $json= json_encode($object, JSON_PRETTY_PRINT);
          echo $json;
?>

