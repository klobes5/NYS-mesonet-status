<!DOCTYPE html>
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
//Get Data from the last 24 hours
	//$sql = "(select stationId ,reportTime, SUM(missed) from siteMisses where reportTime BETWEEN  (NOW() - INTERVAL 1 DAY) and NOW() and reportTime > ? group BY stationId, UNIX_TIMESTAMP(reportTime) DIV ? ORDER BY stationId ASC), $lastUpdate, $myInterval";
$sql = "select stationId ,reportTime, SUM(missed) as sum from siteMisses where reportTime BETWEEN  '2016-06-02 00:00:00' and '2016-06-03 00:00:00' and reportTime > '2016-06-02 00:00:00' group BY stationId, UNIX_TIMESTAMP(reportTime) DIV 3600 ORDER BY stationId ASC";
    $stmt = $dbh->query($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    //initialize data array
    $data = []; 
    while($row =$stmt->fetch())
    {    
         $time = strtotime($row['reportTime']) * 1000;
        //$time = strtotime($row['lastReportTime']);
         $index = array_search($row['stationId'], $sites);
         $frequency = $row['sum'];
         
         //push the information to data fit for HighCharts
         array_push($data, [$time, $index, $frequency]);
         //echo json to page
         echo json_encode($data);
         
    }
?>

