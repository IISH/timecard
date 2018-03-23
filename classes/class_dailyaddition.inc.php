<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/pdo.inc.php";

class class_dailyaddition {
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

	function __construct($id) {
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

	private function initValues() {
		global $dbConn;

		$query = "SELECT DailyAutomaticAdditions.*, Workcodes.Projectnummer AS workcodeProjectnumber, Workcodes.Description AS workcodeDescription
FROM DailyAutomaticAdditions
	INNER JOIN Workcodes ON DailyAutomaticAdditions.workcode = Workcodes.ID
WHERE DailyAutomaticAdditions.ID=" . $this->getId();

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
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
	 * @return mixed
	 */
	public function getIsDeleted()
	{
		return $this->isDeleted;
	}

	/**
	 * @return mixed
	 */
	public function getIsEnabled()
	{
		return $this->isEnabled;
	}

	/**
	 * @return mixed
	 */
	public function getRatio()
	{
		return $this->ratio;
	}

	/**
	 * @return mixed
	 */
	public function getWorkcode()
	{
		return $this->workcode;
	}

	/**
	 * @return mixed
	 */
	public function getWorkcodeProjectnumber()
	{
		return $this->workcodeProjectnumber;
	}

	/**
	 * @return mixed
	 */
	public function getWorkcodeDescription()
	{
		return $this->workcodeDescription;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}

	/**
	 * @return mixed
	 */
	public function getFirstDate()
	{
		return $this->firstDate;
	}

	/**
	 * @return mixed
	 */
	public function getLastDate()
	{
		return $this->lastDate;
	}
}
