<?php 
/**
 * Class for loading and getting settings from the database
 * @version 0.1 2014-06-05
 */
class Settings {
	private static $is_loaded = false;
	private static $settings = null;

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		global $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$arr = array();

		$result = mysql_query('SELECT * FROM settings ', $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[ $row["property"] ] = $row["value"];
			}
			mysql_free_result($result);

		}

		self::$settings = $arr;
		self::$is_loaded = true;
	}

	/**
	 * Return the value of the setting
	 *
	 * @param string $setting_name The name of the setting
	 * @return string The value of the setting
	 */
	public static function get($setting_name) {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = self::$settings[$setting_name];

		return $value;
	}

	public static function save( $setting_name, $value, $settingsTable = '' ) {
		global $settings, $databases;
		$setting_name = trim($setting_name);

		$settingsTable = trim($settingsTable);

		if ( $settingsTable == '' ) {
			$settingsTable = 'settings';
		}

		if ( $setting_name != '' ) {
			$oConn = new class_mysql($databases['default']);
			$oConn->connect();

			$query = "SELECT * FROM `$settingsTable` WHERE `property`='" . $setting_name . "' ";
			$result = mysql_query($query);
			$num_rows = mysql_num_rows($result);

			if ($num_rows > 0) {
				$result = mysql_query("UPDATE `$settingsTable` SET `value`='" . addslashes($value) . "' WHERE `property`='" . $setting_name . "' ", $oConn->getConnection());
			}
			else {
				$result = mysql_query("INSERT INTO `$settingsTable` (`value`, `property`) VALUES ( '" . addslashes($value) . "', '" . $setting_name . "' ) ", $oConn->getConnection());
			}
		}
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
