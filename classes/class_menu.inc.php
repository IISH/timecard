<?php 
$menu = new class_menu();

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: TIMECARD
$menu->addMenuGroup( new class_menugroup('Timecard') );
$menu->addMenuItem( new class_menuitem('timecard.day', 'Day', 'day.php?d={date}') );
$menu->addMenuItem( new class_menuitem('timecard.month', 'Month', 'month.php?d={date}') );
$menu->addMenuItem( new class_menuitem('timecard.quarter', 'Quarter', 'quarter.php?d={date}') );
$menu->addMenuItem( new class_menuitem('timecard.quartertotals', 'Quarter Totals', 'quartertotals.php?d={date}') );

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: TIMECARD (ADMIN)
$menu->addMenuGroup( new class_menugroup('Administrator') );
if ( $oWebuser->hasAdminAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('administrator.day', 'Day', 'admin_day.php?d={date}&eid={eid}') );
	$menu->addMenuItem( new class_menuitem('administrator.month', 'Month', 'admin_month.php?d={date}&eid={eid}') );
	$menu->addMenuItem( new class_menuitem('administrator.quarter', 'Quarter', 'admin_quarter.php?d={date}&eid={eid}') );
	$menu->addMenuItem( new class_menuitem('administrator.quartertotals', 'Quarter Totals', 'admin_quartertotals.php?d={date}&eid={eid}') );
	$menu->addMenuItem( new class_menuitem('administrator.protimeabsenties', 'Protime Absences', 'admin_protime_absences.php') );
	$menu->addMenuItem( new class_menuitem('administrator.crontab', 'Crontab', 'crontab.php') );
	$menu->addMenuItem( new class_menuitem('administrator.change_user', 'Switch user', 'switch_user.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: PROJECTS
$menu->addMenuGroup( new class_menugroup('Projects') );
if ( $oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('projects.project_hour_totals', 'Project totals', 'project_employee_totals.php') );
}
if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasDepartmentAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('projects.hoursleft', 'Hours for planning', 'hoursleft.php') );
	$menu->addMenuItem( new class_menuitem('projects.vastwerk', 'Vast werk', 'vast_werk.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: REPORTS
$menu->addMenuGroup( new class_menugroup('Exports') );
if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('exports.projectemployeetotaals', 'Project totals', 'export_project_employee_totals.php') );
	$menu->addMenuItem( new class_menuitem('exports.euprojects', 'Employee totals', 'admin_euprojecten_overzichten.php') );
	$menu->addMenuItem( new class_menuitem('exports.oracle', 'Oracle', 'export_oracle.php') );
	$menu->addMenuItem( new class_menuitem('exports.misc', 'Miscellaneous', 'misc.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: FINANCIELE ADMINISTRATIE
$menu->addMenuGroup( new class_menugroup('Financial Administration') );
if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('finad.employees', 'Employees', 'employees.php') );
	$menu->addMenuItem( new class_menuitem('finad.projects', 'Projects', 'projects.php') );
	$menu->addMenuItem( new class_menuitem('finad.worklocations', 'Work locations', 'worklocations.php') );
	$menu->addMenuItem( new class_menuitem('finad.not_linked_employees', 'Not Linked Employees', 'admin_not_linked_employees.php') );
	$menu->addMenuItem( new class_menuitem('finad.feestdagen', 'National holidays', 'finad_nationalholidays.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: PERSONAL PAGES
$menu->addMenuGroup( new class_menugroup('Personal pages') );
if ( $oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('pp.personalinfo', 'About me', 'aboutme.php') );
}
if ( $oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('pp.shortcuts', 'Shortcuts', 'shortcuts.php') );
	$menu->addMenuItem( new class_menuitem('pp.dailyautomaticadditions', 'Daily automatic additions', 'dailyautomaticadditions.php') );
}
if ( $oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('pp.feestdagen', 'National holidays', 'nationalholidays.php') );
}
if ( !$oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('pp.login', 'Login', 'login.php') );
}
$menu->addMenuItem( new class_menuitem('pp.contact', 'Contact', 'contact.php') );

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

class class_menuitem {
	// TODOTODO private

	public $code = '';
	public $label = '';
	public $url = '';

	function class_menuitem($code, $label, $url ) {
		$this->code = $code;
		$this->label = $label;
		$this->url = $url;
	}

	function getCode() {
		return $this->code;
	}

	function getLabel() {
		return $this->label;
	}

	function getUrl() {
		return $this->url;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\ncode: " . $this->code . "\n";
	}
}

class class_menugroup {
	// TODOTODO private

	public $code = '';
	public $label = '';
	public $menuitems = array();
	public $counter = 0;

	function class_menugroup($label, $code = '') {
		$this->code = $code;
		$this->label = $label;
	}

	function getCode() {
		return $this->code;
	}

	function getLabel() {
		return $this->label;
	}

	function addMenuItem( $menuitem ) {
		$this->menuitems[] = $menuitem;
	}

	function getMenuItems() {
		return $this->menuitems;
	}

	function showMenuItems() {
		for ( $i = 0; $i < count($this->menuitems); $i++ ) {
			echo '- ' . $this->menuitems[$i]->getLabel() . '<br>';
		}
	}

	function getMenuItemsSubset() {
		$menuitemssubset = array();

		for ( $i = 0; $i < count($this->menuitems); $i++ ) {
			$a = $this->menuitems[$i];

			$menuitemssubset[] = new class_menuitem( $a->getCode(), $a->getLabel(), $a->getUrl() );
		}
		return $menuitemssubset;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\ncode: " . $this->code . "\n";
	}
}

class class_menu {
	// TODOTODO private
	public $menu = array();

	function addMenuGroup( $menugroup ) {
		$this->menu[] = $menugroup;
	}

	function addMenuItem( $menuitem ) {
		$this->menu[count($this->menu)-1]->addMenuItem($menuitem);
	}

	function show() {
		for ( $i = 0; $i < count($this->menu); $i++ ) {
			echo $this->menu[$i]->getLabel() . '<br>';
			echo $this->menu[$i]->showMenuItems();
		}
	}

	function getMenuSubset() {
		$menusubset = new class_menu();

		for ( $i = 0; $i < count($this->menu); $i++ ) {

			$menuitemssubset = $this->menu[$i]->getMenuItemsSubset();

			if ( count($menuitemssubset) > 0 ) {
				// 
				$menusubset->addMenuGroup( new class_menugroup($this->menu[$i]->getLabel() ) );

				foreach ( $menuitemssubset as $mitem ) {
					$menusubset->addMenuItem( $mitem );
				}
			}
		}

		return $menusubset;
	}

	function findTabNumber( $code ) {
		$nr = 0;

		$counter = 0;

		foreach ( $this as $a=>$b ) {
			foreach ( $b as $c ) {

				foreach ( $c->getMenuItems() as $mitem ) {
					if ( $mitem->getCode() == $code ) {
						$nr = $counter;
					}
				}

				$counter++;
			}
		}

		return $nr;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
