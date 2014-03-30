<?php 
require_once("./classes/class_form/class_form.inc.php");

class employee_class_form extends class_form
{
	// TODOEXPLAIN
	function postSave() {
		$oUser = new class_employee( $this->m_doc_id, $this->settings );
		syncProtimeAndTimecardEmployeeData( $oUser->getTimecardId(), $oUser->getProtimeId() );

		//
		parent::postSave();

		return true;
	}
}
?>