<?php
class class_website_protection {
	// construct
	function class_website_protection() {
	}

	function send_warning_mail($tekst) {
		$message = '';

		$recipients = trim(class_settings::getSetting("admin_email"));

		$recipients = str_replace(array(';', ':', ' '), ',', $recipients);

		// fix multiple commas
		if( strpos($recipients, ',,') !== false ) {
			$recipients = str_replace(',,', ',', $recipients);
		}

		if ( $recipients != '' ) {
			$fromname = 'IISG Web protectie';
			$fromaddress = 'noreply@iisg.nl';
			$eol = "\n";

			$headers = "From: " . $fromname . " <" . $fromaddress . ">";

			$subject = "IISG Website warning";

			$iplocator = "http://www.aboutmyip.com/AboutMyXApp/IP2Location.jsp?ip=";

			$message = $message . "Date: " . date("Y-m-d") . $eol;
			$message = $message . "Time: " . date("H:i:s") . $eol;
			$message = $message . "URL: " . $this->getLongUrl() . $eol;
			$message = $message . "IP address: " . $this->get_remote_addr() . $eol;
			$message = $message . "IP Location: " . $iplocator . $this->get_remote_addr() . $eol;
			$message = $message . $eol;
			$message = $message . "Warning: " . $tekst;

			// send e-mail
			mail($recipients, $subject, $message, $headers);
		}
	}

	function getShortUrl() {
		$ret = $_SERVER["QUERY_STRING"];
		if ( $ret != '' ) {
			$ret = "?" . $ret;
		}
		$ret = $_SERVER["SCRIPT_NAME"] . $ret;

		return $ret;
	}

	function getLongUrl() {
		return 'https://' . ( $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $this->getShortUrl();
	}

	function get_remote_addr() {
		$retval = trim($_SERVER["HTTP_X_FORWARDED_FOR"]);
		if ( $retval == '' ) {
			$retval = trim($_SERVER["REMOTE_ADDR"]);
		}

		return $retval;
	}

	// Send error message to browser
	function send_error_to_browser($text) {
		echo fillTemplate(class_settings::getSetting("error_to_browser"), array('text' => $text));
	}

	function check_for_xss_code($tekst, $tekst_is_integer) {
		$foundxss = 0;

		$test = $tekst;
		$test = trim($test);

		if ($test != '') {
			// do first some modifications
			$test = str_replace("+", ' ', $test);
			$test = strtolower($test);

			while ( strpos($test, '  ') !== false ) {
				$test = str_replace('  ',' ', $test);
			}

			$test = str_replace("%3b", ';', $test);
			$test = str_replace("; ", ';', $test);

			$test = str_replace(";;;", ';', $test);
			$test = str_replace(";;", ';', $test);

			$test = trim($test);

			// start controle op XSS values
			if ( $tekst_is_integer == 1 ) {
				// deze teksten zijn niet toegestaan in cijfer velden, maar in andere velden wel
				$foundxss = $this->check_instr_xss($foundxss, $test, ".php");
				$foundxss = $this->check_instr_xss($foundxss, $test, ".asp");
				$foundxss = $this->check_instr_xss($foundxss, $test, "index.");
				$foundxss = $this->check_instr_xss($foundxss, $test, "http://");
				$foundxss = $this->check_instr_xss($foundxss, $test, "https://");
				$foundxss = $this->check_instr_xss($foundxss, $test, "ftp://");
			}

			// deze teksten zijn niet toegestaan in alle soorten velden (die gecontroleerd worden)
			$foundxss = $this->check_instr_xss($foundxss, $test, "%0d%0a");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";delete ");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";select ");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";drop ");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";insert ");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";exec ");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";exec(");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";declare @");
			$foundxss = $this->check_instr_xss($foundxss, $test, ";create ");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.table");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.column");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.key");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.domain");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.parameter");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.routine");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.schema");
			$foundxss = $this->check_instr_xss($foundxss, $test, "information_schema.view");
			$foundxss = $this->check_instr_xss($foundxss, $test, "content-transfer-encoding");
			$foundxss = $this->check_instr_xss($foundxss, $test, "@@version");
			$foundxss = $this->check_instr_xss($foundxss, $test, "@@servername");
			$foundxss = $this->check_instr_xss($foundxss, $test, "db_name()");
			$foundxss = $this->check_instr_xss($foundxss, $test, "user char(");
			$foundxss = $this->check_instr_xss($foundxss, $test, " and user>0");
			$foundxss = $this->check_instr_xss($foundxss, $test, " and 1=1 and ");
			$foundxss = $this->check_instr_xss($foundxss, $test, " and 1=2 and ");
			$foundxss = $this->check_instr_xss($foundxss, $test, " or ''=''");
			$foundxss = $this->check_instr_xss($foundxss, $test, " or '1'='1'");
			$foundxss = $this->check_instr_xss($foundxss, $test, " or 'true'='true'");
			$foundxss = $this->check_instr_xss($foundxss, $test, " or 'false'='false'");
			$foundxss = $this->check_instr_xss($foundxss, $test, "convert(int,");
			$foundxss = $this->check_instr_xss($foundxss, $test, "(select user)");
			$foundxss = $this->check_instr_xss($foundxss, $test, "is_srvrolemember");
			$foundxss = $this->check_instr_xss($foundxss, $test, "is_member(");
			$foundxss = $this->check_instr_xss($foundxss, $test, "order by table_name");
			$foundxss = $this->check_instr_xss($foundxss, $test, "--sp_password");
			$foundxss = $this->check_instr_xss($foundxss, $test, "master..xp_cmdshell");
			$foundxss = $this->check_instr_xss($foundxss, $test, "get nc.exe");
			$foundxss = $this->check_instr_xss($foundxss, $test, "(select top ");
			$foundxss = $this->check_instr_xss($foundxss, $test, "thread/classes/core/");
			$foundxss = $this->check_instr_xss($foundxss, $test, "thread/administrator/components/");
			$foundxss = $this->check_instr_xss($foundxss, $test, "http://cotine.net/");
			$foundxss = $this->check_instr_xss($foundxss, $test, "?webappcfg[APPPATH]");
			$foundxss = $this->check_instr_xss($foundxss, $test, "/themes/runcms/");
			$foundxss = $this->check_instr_xss($foundxss, $test, " union all select ");
			$foundxss = $this->check_instr_xss($foundxss, $test, ",@@version_compile_os,");
			$foundxss = $this->check_instr_xss($foundxss, $test, "union select 1,concat");
			$foundxss = $this->check_instr_xss($foundxss, $test, "/etc/passwd");
			$foundxss = $this->check_instr_xss($foundxss, $test, "/etc/resolv.conf");
			$foundxss = $this->check_instr_xss($foundxss, $test, "../../../../..");

			// still okay?
			if ($foundxss != 0) {
				// not okay
				$this->send_error_to_browser("ERROR 5298765");
				$this->send_warning_mail("XSS blocked - ERROR 5298765 - XSS found in value: " . $tekst);
				die('');
			}
		}
	}

	function check_instr_xss($foundxss, $test, $searchvalue) {
		if ( $foundxss == 0 ) {
			if ( strpos($test, $searchvalue) !== false ) {
				$foundxss = 1;
			}
		}
		return $foundxss;
	}

	function get_value($type = 'get', $field = '') {
		$type = strtolower(trim($type));

		switch ($type) {

			case 'get':

				if ($field == '') {
					$retval = $_GET;
					if ( is_array($retval) ) {
						$retval = implode(';', $retval);
					}
				} else {
					if (isset($_GET[$field])) {
						$retval = $_GET[$field];
					} else {
						$retval = '';
					}
				}

				break;

			case 'post':

				if ($field == '') {
					$retval = $_POST;
					if ( is_array($retval) ) {
						$retval = implode(';', $retval);
					}
				} else {
					if (isset($_POST[$field])) {
						$retval = $_POST[$field];
					} else {
						$retval = '';
					}
				}

				break;

			case 'cookie':

				if ($field == '') {
					$retval = $_COOKIE;
					if ( is_array($retval) ) {
						$retval = implode(';', $retval);
					}
				} else {
					if (isset($_COOKIE[$field])) {
						$retval = $_COOKIE[$field];
					} else {
						$retval = '';
					}
				}

				break;

			case 'value':

				$retval = $field;

				break;

			default:
				die('Error 85163274. Unknown type: ' . $type);
		}

		return $retval;
	}

	function request($type = '', $field = '', $pattern = '') {
		$retval = $this->get_value($type, $field);

		if ($retval != '') {
			// check for xss code
			$this->check_for_xss_code($retval, 0);

			if ($pattern != '') {
				if ( preg_match($pattern, $retval) == 0) {
					// niet goed
					$this->send_error_to_browser("ERROR 8564125");
					$this->send_warning_mail("ERROR 8564125 - command: " . $type . " - value: " . $retval);
					die('');
				}
			}
		}

		return $retval;
	}

	function request_textarea($type = '', $field = '') {
		$retval = $this->get_value($type, $field);

		return $retval;
	}

	function request_positive_number_or_empty($type = '', $field = '') {
		$retval = $this->get_value($type, $field);

		$retval = trim($retval);

		if ($retval != '') {

			// check for xss code
			$this->check_for_xss_code($retval, 1);

			// check if only numbers
			$pattern = "/^[0-9]+$/";

			if ( preg_match($pattern, $retval) == 0) {
				// niet goed
				$this->send_error_to_browser("ERROR 5474582");
				$this->send_warning_mail("ERROR 5474582 - command: " . $type . " - value: " . $retval);
				die('');
			}
		}

		return $retval;
	}

	function request_negative_or_positive_number_or_empty($type = '', $field = '') {
		$retval = $this->get_value($type, $field);

		$retval = trim($retval);

		if ($retval != '') {
			// check for xss code
			$this->check_for_xss_code($retval, 1);

			// check if only numbers
			$pattern = "/^\-?[0-9]+$/";

			if ( preg_match($pattern, $retval) == 0) {
				// niet goed
				$this->send_error_to_browser("ERROR 6521456");
				$this->send_warning_mail("ERROR 6521456 - command: " . $type . " - value: " . $retval);
				die('');
			}
		}

		return $retval;
	}

	function request_only_characters_or_empty($type = '', $field = '') {
		$retval = $this->get_value($type, $field);

		$retval = trim($retval);

		if ($retval != '') {
			// check for xss code
			$this->check_for_xss_code($retval, 0);

			// check if only numbers
			$pattern = "/^[a-zA-Z]+$/";

			if ( preg_match($pattern, $retval) == 0) {
				// niet goed
				$this->send_error_to_browser("ERROR 9856325");
				$this->send_warning_mail("ERROR 9856325 - command: " . $type . " - value: " . $retval);
				die('');
			}
		}

		return $retval;
	}

	function request_only_characters_or_numbers_or_empty($type = '', $field = '') {
		$retval = $this->get_value($type, $field);

		$retval = trim($retval);

		if ($retval != '') {
			// check for xss code
			$this->check_for_xss_code($retval, 0);

			// check if only numbers
			$pattern = "/^[0-9a-zA-Z]+$/";

			if ( preg_match($pattern, $retval) == 0) {
				// niet goed
				$this->send_error_to_browser("ERROR 9456725");
				$this->send_warning_mail("ERROR 9456725 - command: " . $type . " - value: " . $retval);
				die('');
			}
		}

		return $retval;
	}

	function get_left_part($text, $search = ' ' ) {
		$pos = strpos($text, $search);
		if ( $pos !== false ) {
			$text = substr($text, 0, $pos);
		}

		return $text;
	}

	function send_email( $recipients, $subject, $message ) {
		// check recipients
		$recipients = trim($recipients);
		$recipients = str_replace(array(';', ':', ' '), ',', $recipients);
		// fix multiple commas
		while ( strpos($recipients, ',,') !== false ) {
			$recipients = str_replace(',,', ',', $recipients);
		}

		if ( $recipients != '' ) {
			$fromname = 'IISG Timecard';
			$fromaddress = 'noreply@iisg.nl';

			$headers = "From: " . $fromname . " <" . $fromaddress . ">";

			// send e-mail
			mail($recipients, $subject, $message, $headers);

		}
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
