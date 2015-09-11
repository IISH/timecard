<?php 
require_once dirname(__FILE__) . "/class_file.inc.php";
require_once dirname(__FILE__) . "/class_misc.inc.php";

class class_page {
	private $page_template;
	private $settings;
	private $remove_sidebar;
	private $content;
	private $shortcuts;
	private $departmentShortcuts;
	private $recentlyused;
	private $tab;
	private $title;
	private $color;
	private $left_menu;
	private $cssextension;

	function class_page($page_template, $settings) {
		$this->page_template = $page_template;
		$this->settings = $settings;
		$this->remove_sidebar = 0;
		$this->content = '';
		$this->shortcuts = '';
		$this->departmentShortcuts = '';
		$this->recentlyused = '';
		$this->tab = 0;
		$this->title = '';
		$this->color = '73A0C9';
		$this->left_menu = '';
		$this->cssextension = '';
	}

	function getPage() {
		global $oWebuser, $protect;

		$oFile = new class_file();
		$page = $oFile->getFileSource($this->page_template);
		$page = str_replace('{url}', $this->getUrl(), $page);

		$page = str_replace('{content}', $this->getContent(), $page);
		$page = str_replace('{shortcuts}', $this->getShortcuts(), $page);
		$page = str_replace('{departmentshortcuts}', $this->getDepartmentShortcuts(), $page);
		$page = str_replace('{recentlyused}', $this->getRecentlyUsed(), $page);

		$page = str_replace('{opentab}', $this->getTab(), $page);

		$page = str_replace('{title}', $this->getTitle(), $page);
		$page = str_replace('{color}', $this->getColor(), $page);
		$page = str_replace('{cssextension}', $this->cssextension, $page);

		if ( $this->left_menu == '' ) {
			$page = str_replace('{extraleftmenuclass}', ' hidden', $page);
		} else {
			$page = str_replace('{leftmenu}', $this->left_menu, $page);
		}

		if ( $this->remove_sidebar == 1 || ( $this->getShortcuts() == '' && $this->getRecentlyUsed() == '' ) ) {
			$page = str_replace('{extrasidebarclass}', ' hidden', $page);
			if ( $this->left_menu != '' ) {
				$page = str_replace('{extracontentclass}', 'contentfullwidth_admin', $page);
			} else {
				$page = str_replace('{extracontentclass}', 'contentfullwidth', $page);
			}
		}

		if ( $this->getShortcuts() == '' ) {
			$page = str_replace('{extrashortcutsclass}', ' hidden', $page);
		} else {
		}

		if ( $this->getRecentlyUsed() == '' ) {
			$page = str_replace('{extrarecentlyusedclass}', ' hidden', $page);
		}

		$page = str_replace('{menu}', $this->createMenu(), $page);

		// 
		$welcome = 'Welcome';
		$logout = '';
		if ( $oWebuser->isLoggedIn() ) {
			if ( trim( $oWebuser->getFirstLastname() ) != '' ) {
				$welcome .= ', ' . trim( $oWebuser->getFirstLastname() );
			}
			$logout = '<a href="logout.php" onclick="if (!confirm(\'Please confirm logout\')) return false;">(logout)</a>';
		}
		$page = str_replace('{welcome}', $welcome, $page);
		$page = str_replace('{logout}', $logout, $page);

		// als laatste
		$page = str_replace('{date}', class_datetime::getQueryDate(), $page);
		$page = str_replace('{eid}', $protect->request('get', 'eid'), $page);

		$page = $this->removeUnusedTags($page);

		return $page;
	}

	function createMenu() {
		global $menuList;

		// GROUPS
		$sMenu = "<div id=\"tabs\">
		<ul>
";

		// TODOTODO MODIFY
		foreach ( $menuList as $a=>$b ) {
			$counter = 0;

			foreach ( $b as $c ) {
				$sMenu .= "			<li><a href=\"#tabs-" . $counter . "\">" . $c->getLabel() . "</a></li>\n";
				$counter++;
			}
		}

		$sMenu .= "		</ul>
";

		// FOR EACH GROUP ADD ITEMS
		$counter = 0;
		// TODOTODO MODIFY
		foreach ( $menuList as $a=>$b ) {
			foreach ( $b as $c ) {
				$sMenu .= "		<div id=\"tabs-" . $counter . "\">
			<ul>
";
				foreach ( $c->getMenuItems() as $mitem ) {
					$sMenu .= "				<li><a href=\"" . $mitem->getUrl() . "\">" . $mitem->getLabel() . "</a></li>\n";
				}

				$sMenu .= "			</ul>
		</div>
";
				$counter++;
			}
		}

		$sMenu .= "	</div>
";

		return $sMenu;
	}

	function removeUnusedTags($page) {
		$page = str_replace('{extrasidebarclass}', '', $page);
		$page = str_replace('{extracontentclass}', '', $page);
		$page = str_replace('{extrashortcutsclass}', '', $page);
		$page = str_replace('{extrarecentlyusedclass}', '', $page);

		return $page;
	}

	function getUrl() {
		return 'https://' . ( isset( $_SERVER["HTTP_X_FORWARDED_HOST"] ) && $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $_SERVER["SCRIPT_NAME"];
	}

	function removeSidebar() {
		$this->remove_sidebar = 1;
	}

	function setContent( $content ) {
		$this->content = $content;
	}

	function setCssExtension( $css ) {
		$this->cssextension = $css;
	}

	function getContent() {
		return $this->content;
	}

	function setLeftMenu( $left_menu ) {
		$this->left_menu = $left_menu;
	}

	function getLeftMenu() {
		return $this->left_menu;
	}

	function setUserShortcuts( $shortcuts ) {
		$this->shortcuts = $shortcuts;
	}

	function setDepartmentShortcuts( $shortcuts ) {
		$this->departmentShortcuts = $shortcuts;
	}

	function getShortcuts() {
		return $this->shortcuts;
	}

	function getDepartmentShortcuts() {
		return $this->departmentShortcuts;
	}

	function setRecentlyUsed( $recentlyused ) {
		$this->recentlyused = $recentlyused;
	}

	function getRecentlyUsed() {
		return $this->recentlyused;
	}

	function setTab( $tab ) {
		$this->tab = $tab;
	}

	function getTab() {
		$tab = $this->tab;
		return $tab;
	}

	function setTitle( $title ) {
		$this->title = $title;
	}

	function getTitle() {
		return $this->title;
	}

	function setColor( $color ) {
		$this->color = $color;
	}

	function getColor() {
		return $this->color;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\ntemplate: " . $this->page_template . "\n";
	}
}
