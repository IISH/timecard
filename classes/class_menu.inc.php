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
$menu->addMenuGroup( new class_menugroup('Timecard (admin)') );
if ( $oWebuser->hasAdminAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('administrator.day', 'Day', 'admin_day.php?d={date}') );
	$menu->addMenuItem( new class_menuitem('administrator.month', 'Month', 'admin_month.php?d={date}') );
	$menu->addMenuItem( new class_menuitem('administrator.quarter', 'Quarter', 'admin_quarter.php?d={date}') );
	$menu->addMenuItem( new class_menuitem('administrator.quartertotals', 'Quarter Totals', 'admin_quartertotals.php?d={date}') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: MISC
$menu->addMenuGroup( new class_menugroup('Miscellaneous') );
if ( $oWebuser->hasAdminAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('misc.protimeabsenties', 'Protime Absences', 'admin_protime_absences.php') );
	$menu->addMenuItem( new class_menuitem('misc.urenperweek', 'Hours per week', 'admin_hoursperweek.php') );
	$menu->addMenuItem( new class_menuitem('reports.hoursleft', 'Hours left', 'admin_hoursleft.php') );
	$menu->addMenuItem( new class_menuitem('misc.not_linked_employees', 'Not Linked Employees', 'admin_not_linked_employees.php') );
	$menu->addMenuItem( new class_menuitem('administrator.change_user', 'Switch user', 'switch_user.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: REPORTS
$menu->addMenuGroup( new class_menugroup('Exports') );
if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasExportsAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('exports.euprojecten', 'Employee Project totals', 'admin_euprojecten_overzichten.php') );
	$menu->addMenuItem( new class_menuitem('exports.oracle', 'Oracle', 'export_oracle.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: FINANCIELE ADMINISTRATIE
$menu->addMenuGroup( new class_menugroup('Financial Administration') );
if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) {
	$menu->addMenuItem( new class_menuitem('finad.employees', 'Employees', 'employees.php') );
	$menu->addMenuItem( new class_menuitem('finad.projecten', 'Projects', 'projects.php') );
	$menu->addMenuItem( new class_menuitem('finad.worklocations', 'Work locations', 'worklocations.php') );
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: PERSONAL PAGES
$menu->addMenuGroup( new class_menugroup('Personal pages') );
if ( $oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('pp.personalinfo', 'About me', 'aboutme.php') );
}
if ( $oWebuser->isLoggedIn() ) {
	$menu->addMenuItem( new class_menuitem('pp.myshortcuts', 'Shortcuts', 'myshortcuts.php') );
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

	// TODOEXPLAIN
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

	// TODOEXPLAIN
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

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
