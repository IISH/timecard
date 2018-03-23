<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/pdo.inc.php";

class class_contentdesign {
	private $id;
	private $design;
	private $header;
	private $content;
	private $records;
	private $footer;
	private $isDeleted;

	function __construct($design) {
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
		global $dbConn;

		$query = "SELECT * FROM `Contentdesign` WHERE `design`='" . $this->design . "' ";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$r = $stmt->fetch();

		$this->id = $r["ID"];
		$this->header = $r["header"];
		$this->content = $r["content"];
		$this->records = $r["records"];
		$this->footer = $r["footer"];
		$this->isDeleted = $r["isdeleted"];
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
			$t = str_replace('[' . strtoupper($key) . ']', Settings::get( strtolower($key) ), $t);
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
