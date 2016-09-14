select stationId, lastReportTime, count(*) as count
from SiteStatusHistory_dev.StationReport_tbl 
WHERE lastReportTime BETWEEN '2016-09-13 00:00:00' and '2016-09-13 00:30:00' 
Group BY stationId, UNIX_TIMESTAMP(lastReportTime) DIV 300 ORDER BY stationId ASC