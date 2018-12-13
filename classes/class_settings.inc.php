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
		global $dbConn;

		$arr = array();

		$query = 'SELECT * FROM settings ';
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[ $row["property"] ] = $row["value"];
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
		global $dbConn;
		$setting_name = trim($setting_name);

		$settingsTable = trim($settingsTable);

		if ( $settingsTable == '' ) {
			$settingsTable = 'settings';
		}

		if ( $setting_name != '' ) {
			$query = "SELECT * FROM `$settingsTable` WHERE `property`='" . $setting_name . "' ";
			$stmt = $dbConn->prepare($query);
			$stmt->execute();

			if ( $row = $stmt->fetch() ) {
				$query = "UPDATE `$settingsTable` SET `value`='" . addslashes($value) . "' WHERE `property`='" . $setting_name . "' ";
			} else {
				$query = "INSERT INTO `$settingsTable` (`value`, `property`) VALUES ( '" . addslashes($value) . "', '" . $setting_name . "' ) ";
			}
			$stmt = $dbConn->prepare($query);
			$stmt->execute();
		}
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
