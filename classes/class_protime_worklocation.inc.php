<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/pdo.inc.php";

class class_protime_worklocation {
	private $locationId;
	private $short_1;
	private $short_2;
	private $description;

	function __construct($id) {
		$this->locationId = $id;
		$this->short_1 = '';
		$this->short_2 = '';
		$this->description = '';

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$query = "SELECT * FROM protime_worklocation WHERE LOCATIONID=" . $this->getLocationId();
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$this->short_1 = $r["SHORT_1"];
			$this->short_2 = $r["SHORT_2"];
			$this->description = $r["DESCRIPTION"];
		}
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

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->locationId . "\nShort_1: " . $this->short_1 . "\n";
	}

	public static function getProtimeWorklocations() {
		global $dbConn;

		$arr_p_wl = array();

		$query = "SELECT * FROM protime_worklocation ORDER BY LOCATIONID";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$arr_p_wl[$r["LOCATIONID"]] = new class_protime_worklocation( $r["LOCATIONID"] );
		}

		return $arr_p_wl;
	}
}