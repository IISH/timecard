<?php
class class_misc01 {

	public static function getMisc01($yyyy_mm) {
		global $databases;

		$ret = array();

		$ret[] = array('Name', 'Description', 'Time (hh:mm)');

		$oConn = new class_mysql($databases['default']);
		$success = $oConn->connect();

		//
		$query = "
SELECT vw_Employees.NAME, vw_Employees.FIRSTNAME, LongCode, WorkDescription, TimeInMinutes
FROM Workhours
	INNER JOIN vw_Employees on Workhours.Employee = vw_Employees.ID
WHERE Workhours.isdeleted = 0
	AND DateWorked LIKE '" . $yyyy_mm . "%'
	AND WorkCode = 4
ORDER BY vw_Employees.FULLNAME, LongCode, WorkDescription ";

		$lastUser = '';
		$lastTask = '';
		$total = 0;

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$currentUser = trim($row["FIRSTNAME"] . ' ' . verplaatsTussenvoegselNaarBegin($row["NAME"]));
				$currentTask = class_misc01::CutTask($row["WorkDescription"]);

				if ( $currentUser == '' ) {
					$currentUser = $row['LongCode'];
				}

				//
				if ( strlen( $currentTask ) <= 3 ) {
					$currentTask = strtoupper($currentTask);
				}

				if ( $lastUser != '' ) {
					if ( $currentUser != $lastUser || strtolower($currentTask) != strtolower($lastTask) ) {
						// save into array
						$ret[] = array($lastUser, $lastTask, class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($total) );
						$total = 0;
					}
				}

				$total += $row["TimeInMinutes"];

				// remember last ...
				$lastUser = $currentUser;
				$lastTask = $currentTask;
			}
			mysql_free_result($result);

		}

		// don't forget to save last record in array
		$ret[] = array($lastUser, $lastTask, class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($total) );

		return $ret;
	}

	public static function CutTask( $task ) {
		$task = trim($task);

		$arrTask = explode(':', $task);
		$task = $arrTask[0];

		$task = trim($task);

		if ( $task == '' ) {
			$task = 'Unknown';
		}

		return $task;
	}
}
