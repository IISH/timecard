<?php 
require_once("./classes/class_form/class_form.inc.php");

class workhours_class_form extends class_form
{
	function postSave() {

		// remove relation on relation break
		$query = "
UPDATE Workhours
	INNER JOIN DailyAutomaticAdditions ON Workhours.daily_automatic_addition_id = DailyAutomaticAdditions.ID
SET daily_automatic_addition_id = NULL, fixed_time = 0
WHERE Workhours.Employee=" . $_POST["FORM_Employee"] . "
	AND DailyAutomaticAdditions.Employee=" . $_POST["FORM_Employee"] . "
	AND Workhours.WorkCode<>DailyAutomaticAdditions.workcode
";

		$res = mysql_query($query, $this->oDb->getConnection()) or die(mysql_error());

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// delete alle records die op status deleted staan
		$query_delete = "DELETE FROM Workhours WHERE isdeleted=1 AND Employee=" . $_POST["FORM_Employee"];
		$res_delete = mysql_query($query_delete, $this->oDb->getConnection()) or die(mysql_error());

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// update lastyear data entered
		$year = date('Y', strtotime($_POST["FORM_DateWorked"]));
		$query_lastyear = "UPDATE Employees SET lastyear=" . $year . " WHERE ID=" . $_POST["FORM_Employee"] . " AND lastyear<" . $year . " AND " . $year . "<=" . (date("Y")+1);
		$res_lastyear = mysql_query($query_lastyear, $this->oDb->getConnection()) or die(mysql_error());

		//
		parent::postSave();

		return true;
	}
}
