<?php 
// modified: 2012-12-02

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";

class class_contentdesign {
	private $settings;
	private $id;
	private $design;
	private $header;
	private $content;
	private $records;
	private $footer;
	private $isDeleted;

	// TODOEXPLAIN
	function class_contentdesign($design) {
		global $settings;

		$this->settings = $settings;
		$this->id = 0;
		$this->design = $design;
		$this->header = '';
		$this->content = '';
		$this->records = '';
		$this->footer = '';
		$this->isDeleted = 0;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		$oConn = new class_mysql($this->settings, 'timecard');
		$oConn->connect();

		$query = "SELECT * FROM `Contentdesign` WHERE `design`='" . $this->design . "' ";

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->id = $r["ID"];
			$this->header = $r["header"];
			$this->content = $r["content"];
			$this->records = $r["records"];
			$this->footer = $r["footer"];
			$this->isDeleted = $r["isdeleted"];
		}
		mysql_free_result($res);
	}

	/**
	 * Get design ID
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get design name/code
	 * @return string
	 */
	public function getDesign()
	{
		return $this->design;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getHeader()
	{
		return $this->header;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getRecords()
	{
		return $this->records;
	}

	/**
	 * TODOEXPLAIN
	 * @return mixed
	 */
	public function getFooter()
	{
		return $this->footer;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\ndesign: " . $this->design . "\n";
	}
}