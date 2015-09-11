<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";

class class_contentdesign {
	private $databases;
	private $id;
	private $design;
	private $header;
	private $content;
	private $records;
	private $footer;
	private $isDeleted;

	function class_contentdesign($design) {
		global $databases;

		$this->databases = $databases;
		$this->id = 0;
		$this->design = $design;
		$this->header = '';
		$this->content = '';
		$this->records = '';
		$this->footer = '';
		$this->isDeleted = 0;

		$this->initValues();
	}

	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
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
	 * @return mixed
	 */
	public function getHeader()
	{
		return $this->header;
	}

	/**
	 * @return mixed
	 */
	public function getContent()
	{
		$t = $this->content;

		$keys = array('cron_key');
		foreach ( $keys as $key ) {
			$t = str_replace('[' . strtoupper($key) . ']', class_settings::getSetting( strtolower($key) ), $t);
		}

		return $t;
	}

	/**
	 * @return mixed
	 */
	public function getRecords()
	{
		return $this->records;
	}

	/**
	 * @return mixed
	 */
	public function getFooter()
	{
		return $this->footer;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\ndesign: " . $this->design . "\n";
	}
}
