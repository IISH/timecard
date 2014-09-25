<?php
// modified: 2014-07-03

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";
require_once "class_mssql.inc.php";

class class_syncProtimeMysql {
	protected $settings = null;
	protected $sourceTable = '';
	protected $targetTable = '';
	protected $primaryKeyField = '';
	protected $fields = array();
	protected $lastInsertId;
	protected $sourceCriterium = '';
	protected $counter = 0;

	// TODOEXPLAIN
	function __construct() {
		global $settings;
		$this->settings = $settings;
	}

	// TODOEXPLAIN
	public function setSourceTable( $sourceTable ) {
		$this->sourceTable = $sourceTable;
	}

	// TODOEXPLAIN
	public function getSourceTable() {
		return $this->sourceTable;
	}

	// TODOEXPLAIN
	public function setTargetTable( $targetTable ) {
		$this->targetTable = $targetTable;
	}

	// TODOEXPLAIN
	public function getTargetTable() {
		return $this->targetTable;
	}

	// TODOEXPLAIN
	public function setPrimaryKey( $primaryKeyField ) {
		$this->primaryKeyField = $primaryKeyField;
	}

	// TODOEXPLAIN
	public function getPrimaryKey() {
		return $this->primaryKeyField;
	}

	// TODOEXPLAIN
	public function addField( $field ) {
		$this->fields[] = $field;
	}

	// TODOEXPLAIN
	public function addFields( $fields ) {
		foreach ( $fields as $field ) {
			$this->fields[] = $field;
		}
	}

	// TODOEXPLAIN
	public function getLastInsertId() {
		return $this->lastInsertId;
	}

	// TODOEXPLAIN
	public function setSourceCriterium( $sourceCriterium ) {
		$this->sourceCriterium = $sourceCriterium;
	}

	// TODOEXPLAIN
	public function getSourceCriterium() {
		return $this->sourceCriterium;
	}

	// TODOEXPLAIN
	public function getCounter() {
		return $this->counter;
	}

	// TODOEXPLAIN
	public function doSync() {
		echo "Sync " . $this->sourceTable . " (KNAW) -> " . $this->targetTable . " (IISG)<br>";

		$oConn = new class_mysql($this->settings, 'timecard');
		$oConn->connect();

		$oPt = new class_mssql($this->settings, 'protime');
		$oPt->connect();

		// set records as being updated
		if ( $this->sourceCriterium != '' ) {
			// subset of records
			$query = "UPDATE " . $this->targetTable . " SET sync_state=2 WHERE " . $this->sourceCriterium;
		} else {
			// all records
			$query = "UPDATE " . $this->targetTable . " SET sync_state=2 ";
		}
//debug($query);
		$resultData = mysql_query($query, $oConn->getConnection());

		//
		$query = "SELECT * FROM " . $this->sourceTable;
		if ( $this->sourceCriterium != '' ) {
			$query .= ' WHERE ' . $this->sourceCriterium . ' ';
		}
		$query .= " ORDER BY " . $this->getPrimaryKey();

		// save counter in table
		class_settings::saveSetting('cron_counter_' . $this->getTargetTable(), $this->counter, $this->getTargetTable() . "_syncinfo");

//debug($query);
		$resultData = mssql_query($query, $oPt->getConnection());
		while ( $rowData = mssql_fetch_array($resultData) ) {
			$this->insertUpdateMysqlRecord($rowData, $oConn);
		}

		//
		mssql_free_result($resultData);

		// remove deleted records
		$query = "DELETE FROM " . $this->targetTable . " WHERE sync_state=2 ";
//debug($query);
		$resultData = mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nsource: " . $this->sourceTable . "\ntarget: " . $this->targetTable . "\n";
	}

	// TODOEXPLAIN
	protected function insertUpdateMysqlRecord($protimeRowData, $oConn) {

		$result = mysql_query("SELECT * FROM " . $this->getTargetTable() . " WHERE " . $this->getPrimaryKey() . "='" . $protimeRowData[$this->getPrimaryKey()] . "' ", $oConn->getConnection());
		$num_rows = mysql_num_rows($result);

		$this->lastInsertId = $protimeRowData[$this->getPrimaryKey()];
		$this->counter++;

		if ($num_rows > 0) {
			// create update query
			$separator = '';
			$query = "UPDATE " . $this->getTargetTable() . " SET ";
			foreach ( $this->fields as $field ) {
				$query .= $separator. $field . "='" . addslashes($protimeRowData[$field]) . "' ";
				$separator = ', ';
			}

			$query .= $separator . " last_refresh='" . date("Y-m-d H:i:s") . "'";
			$query .= $separator . " sync_state=1";

			$query .= " WHERE " . $this->getPrimaryKey() . "='" . $protimeRowData[$this->getPrimaryKey()] . "' ";
		} else {
			// create insert query
			$separator = '';
			$fields = '';
			$values = '';
			foreach ( $this->fields as $field ) {
				$fields .= $separator. $field;
				$values .= $separator. "'" . addslashes($protimeRowData[$field]) . "'";
				$separator = ', ';
			}

			$fields .= $separator. "last_refresh";
			$fields .= $separator. "sync_state";
			$values .= $separator. "'" . date("Y-m-d H:i:s") . "'";
			$values .= $separator. "1";

			$query = "INSERT INTO " . $this->getTargetTable() . " ( $fields ) VALUES ( $values ) ";
		}

		if ( $this->counter % 10 === 0 ) {
			if ( $this->counter % 100 === 0 ) {
				echo $this->counter . ' ';

				// save counter in table
				class_settings::saveSetting('cron_counter_' . $this->getTargetTable(), $this->counter, $this->getTargetTable() . "_syncinfo");
			} else {
				echo '. ';
			}
			flush();
		}

		// save counter in table
		class_settings::saveSetting('cron_counter_' . $this->getTargetTable(), $this->counter, $this->getTargetTable() . "_syncinfo");

		// execute query
		$result = mysql_query($query, $oConn->getConnection());
	}
}
