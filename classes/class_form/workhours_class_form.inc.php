<?php 
require_once("./classes/class_form/class_form.inc.php");

class workhours_class_form extends class_form
{
	function postSave() {
		// delete alle records die op status deleted staan
		$query_delete = "DELETE FROM Workhours WHERE isdeleted=1 ";
		$res_delete = mysql_query($query_delete, $this->oDb->connection()) or die(mysql_error());

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// update lastyear data entered
		$year = date('Y', strtotime($_POST["FORM_DateWorked"]));
		$query_lastyear = "UPDATE Employees SET lastyear=" . $year . " WHERE ID=" . $_POST["FORM_Employee"] . " AND lastyear<" . $year . " AND " . $year . "<=" . (date("Y")+1);
		$res_lastyear = mysql_query($query_lastyear, $this->oDb->connection()) or die(mysql_error());

		//
		parent::postSave();

		return true;
	}
}
?>