<?php 
class class_authentication {

	function __construct() {
	}

	function authenticate( $login, $password ) {
		// TODO: move authentication settings to jira/confluence active directory
		// TODO: after move to jira/confluence ldap, who is allowed to enter data??? (this must be configured somewhere)
		return class_authentication::check_ldap('iisgnet\\' . $login, $password, explode(' ', trim(Settings::get('ms_active_directories'))));
	}

	function check_ldap($user, $pw, $servers) {
		$login_correct = 0;

		// LDAP AUTHENTICATIE VIA PHP-LDAP
		// php-ldap must be installed on the server

		foreach ( $servers as $server ) {
			if ( $login_correct == 0 ) {

				// connect
				$ad = ldap_connect($server) or die ("Could not connect to $server. Please contact IT Servicedesk");

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
			}
		}

        if ( $login_correct == 0 ) {
            error_log("LOGIN FAILED $user from " . class_misc::get_remote_addr() . " (SA: " . trim(Settings::get('ms_active_directories')) . ")");
        }

		return $login_correct;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
