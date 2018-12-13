<?php
/**
 * Class for settings syncinfo
 */
class SyncInfo {
	private static $settings_table = 'Timecard_syncinfo';

	public static function save( $setting_name, $type, $value ) {
		global $dbConn;

		$settingsTable = self::$settings_table;

		if ( $setting_name != '' ) {
			$query = "SELECT * FROM $settingsTable WHERE property='" . $setting_name . "' ";
			$stmt = $dbConn->prepare($query);
			$stmt->execute();

			if ( $row = $stmt->fetch() ) {
				$query = "UPDATE $settingsTable SET $type='" . addslashes($value) . "' WHERE property='" . $setting_name . "' ";
			} else {
				$query = "INSERT INTO $settingsTable (property, $type) VALUES ( '" . $setting_name . "', '" . addslashes($value) . "' ) ";
			}
			$stmt = $dbConn->prepare($query);
			$stmt->execute();
		}
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
