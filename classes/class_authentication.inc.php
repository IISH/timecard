<?php 
// modified: 2012-12-28

class class_authentication {

	// TODOEXPLAIN
	function class_authentication() {
	}

	// TODOEXPLAIN
	function authenticate( $login, $password ) {
		return class_authentication::check_ldap('iisgnet\\' . $login, $password, array("apollo3.iisg.net", "apollo2.iisg.net"));
	}

	// TODOEXPLAIN
	function check_ldap($user, $pw, $servers) {
		$login_correct = 0;

		// LDAP AUTHENTICATIE VIA PHP-LDAP
		// php-ldap moet geinstalleerd zijn op de server

		foreach ( $servers as $server ) {
			if ( $login_correct == 0 ) {

				// connect
				$ad = ldap_connect($server);

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding
				if ($bd) {
					$login_correct = 1;
				}

				// never forget to unbind!
				ldap_unbind($ad);

				if ( trim($contents) == "1" ) {
					$login_correct = 1;
				}
			}
		}

		return $login_correct;
	}
}
?>