<?php 
require_once dirname(__FILE__) . "/class_file.inc.php";
require_once dirname(__FILE__) . "/class_misc.inc.php";

class class_page {
	private $page_template;
	private $settings;
	private $remove_sidebar;
	private $content;
	private $shortcuts;
	private $recentlyused;
	private $tab;
	private $title;
	private $color;
	private $left_menu;
	private $cssextension;

	// TODOEXPLAIN
	function class_page($page_template, $settings) {
		$this->page_template = $page_template;
		$this->settings = $settings;
		$this->remove_sidebar = 0;
		$this->content = '';
		$this->shortcuts = '';
		$this->recentlyused = '';
		$this->tab = 0;
		$this->title = '';
		$this->color = '73A0C9';
		$this->left_menu = '';
		$this->cssextension = '';
	}

	// TODOEXPLAIN
	function getPage() {
		global $oWebuser, $protect;

		$oFile = new class_file();
		$page = $oFile->getFileSource($this->page_template);
		$page = str_replace('{url}', $this->getUrl(), $page);

		$page = str_replace('{content}', $this->getContent(), $page);
		$page = str_replace('{shortcuts}', $this->getShortcuts(), $page);
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
			if ( trim($oWebuser->getFirstLastname()) != '' ) {
				$welcome .= ', ' . trim($oWebuser->getFirstLastname());
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

	// TODOEXPLAIN
	function getUrl() {
		return 'https://' . ( $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $_SERVER["SCRIPT_NAME"];
	}

	// TODOEXPLAIN
	function removeSidebar() {
		$this->remove_sidebar = 1;
	}

	// TODOEXPLAIN
	function setContent( $content ) {
		$this->content = $content;
	}

	// TODOEXPLAIN
	function setCssExtension( $css ) {
		$this->cssextension = $css;
	}

	// TODOEXPLAIN
	function getContent() {
		return $this->content;
	}

	// TODOEXPLAIN
	function setLeftMenu( $left_menu ) {
		$this->left_menu = $left_menu;
	}

	// TODOEXPLAIN
	function getLeftMenu() {
		return $this->left_menu;
	}

	// TODOEXPLAIN
	function setShortcuts( $shortcuts ) {
		$this->shortcuts = $shortcuts;
	}

	// TODOEXPLAIN
	function getShortcuts() {
		return $this->shortcuts;
	}

	// TODOEXPLAIN
	function setRecentlyUsed( $recentlyused ) {
		$this->recentlyused = $recentlyused;
	}

	// TODOEXPLAIN
	function getRecentlyUsed() {
		return $this->recentlyused;
	}

	// TODOEXPLAIN
	function setTab( $tab ) {
		$this->tab = $tab;
	}

	// TODOEXPLAIN
	function getTab() {
		$tab = $this->tab;
		return $tab;
	}

	// TODOEXPLAIN
	function setTitle( $title ) {
		$this->title = $title;
	}

	// TODOEXPLAIN
	function getTitle() {
		return $this->title;
	}

	// TODOEXPLAIN
	function setColor( $color ) {
		$this->color = $color;
	}

	// TODOEXPLAIN
	function getColor() {
		return $this->color;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\ntemplate: " . $this->page_template . "\n";
	}
}
