<?php 
// modified: 2014-06-03

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";

class class_dailyaddition {
	private $settings;
	private $id;
	private $employeeId;
	private $workcode;
	private $workcodeProjectnumber;
	private $workcodeDescription;
	private $description;
	private $isEnabled;
	private $isDeleted;
	private $ratio;
	private $firstDate;
	private $lastDate;

	// TODOEXPLAIN
	function class_dailyaddition($id) {
		global $settings;
		$this->settings = $settings;
		$this->id = $id;
		$this->employeeId = 0;
		$this->workcode = 0;
		$this->workcodeProjectnumber = '';
		$this->workcodeDescription = '';
		$this->description = '';
		$this->isEnabled = 0;
		$this->isDeleted = 0;
		$this->ratio = 0;
		$this->firstDate = '';
		$this->lastDate = '';

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		$oConn = new class_mysql($this->settings, 'timecard');
		$oConn->connect();

		$query = "SELECT DailyAutomaticAdditions.*, Workcodes.Projectnummer AS workcodeProjectnumber, Workcodes.Description AS workcodeDescription
FROM DailyAutomaticAdditions
	INNER JOIN Workcodes ON DailyAutomaticAdditions.workcode = Workcodes.ID
WHERE DailyAutomaticAdditions.ID=" . $this->getId();

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->employeeId = $r["employee"];
			$this->workcode = $r["workcode"];
			$this->workcodeProjectnumber = $r["workcodeProjectnumber"];
			$this->workcodeDescription = $r["workcodeDescription"];
			$this->description = $r["description"];
			$this->isEnabled = $r["isenabled"];
			$this->isDeleted = $r["isdeleted"];
			$this->ratio = $r["ratio"];
			$this->firstDate = $r["first_date"];
			$this->lastDate = $r["last_date"];
		}
		mysql_free_result($res);
	}

	/**
	 * Get daily addition ID
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get employee ID
	 * @return integer
	 */
	public function getEmployeeId() {
		return $this->employeeId;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getIsDeleted()
	{
		return $this->isDeleted;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getIsEnabled()
	{
		return $this->isEnabled;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getRatio()
	{
		return $this->ratio;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getWorkcode()
	{
		return $this->workcode;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getWorkcodeProjectnumber()
	{
		return $this->workcodeProjectnumber;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getWorkcodeDescription()
	{
		return $this->workcodeDescription;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getFirstDate()
	{
		return $this->firstDate;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getLastDate()
	{
		return $this->lastDate;
	}
}
