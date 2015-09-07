<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";

class class_protime_worklocation {
	private $databases;
	private $locationId;
	private $short_1;
	private $short_2;
	private $description;

	// TODOEXPLAIN
	function class_protime_worklocation($id) {
		global $databases;
		$this->databases = $databases;

		$this->locationId = $id;
		$this->short_1 = '';
		$this->short_2 = '';
		$this->description = '';

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM PROTIME_WORKLOCATION WHERE LOCATIONID=" . $this->getLocationId();

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->short_1 = $r["SHORT_1"];
			$this->short_2 = $r["SHORT_2"];
			$this->description = $r["DESCRIPTION"];
		}
		mysql_free_result($res);
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return mixed
	 */
	public function getLocationId()
	{
		return $this->locationId;
	}

	/**
	 * @param mixed $locationId
	 */
	public function setLocationId($locationId)
	{
		$this->locationId = $locationId;
	}

	/**
	 * @return string
	 */
	public function getShort1()
	{
		return $this->short_1;
	}

	/**
	 * @param string $short_1
	 */
	public function setShort1($short_1)
	{
		$this->short_1 = $short_1;
	}

	/**
	 * @return string
	 */
	public function getShort2()
	{
		return $this->short_2;
	}

	/**
	 * @param string $short_2
	 */
	public function setShort2($short_2)
	{
		$this->short_2 = $short_2;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->locationId . "\nShort_1: " . $this->short_1 . "\n";
	}

	// TODOEXPLAIN
	public static function getProtimeWorklocations() {
		global $databases;

		$arr_p_wl = array();

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM PROTIME_WORKLOCATION ORDER BY LOCATIONID";

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$arr_p_wl[$r["LOCATIONID"]] = new class_protime_worklocation( $r["LOCATIONID"] );
		}
		mysql_free_result($res);

		return $arr_p_wl;
	}
}
