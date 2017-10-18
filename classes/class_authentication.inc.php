<?php 
class class_authentication {

	function authenticate( $login, $password ) {
		// IISG Active Directory
		if ( strpos($login, '.') !== false ) {
			// if login contains 'dot' then authenticate with Microsoft Active Directory
			return class_authentication::check_ad($login, $password, 'iisg');
		} // KNAW LDAP
		else {
			// if login does not contain 'dot' then authenticate with LDAP
			return class_authentication::check_ldap($login, $password, 'knaw');
		}
	}

	function check_ad($user, $pw, $servers) {
		global $active_directories;
		$login_correct = 0;

		// LDAP AUTHENTICATIE VIA PHP-LDAP
		// php-ldap must be installed on the server

		foreach ( $active_directories as $server ) {
			if ( $login_correct == 0 ) {

				// connect
				$ad = ldap_connect($server) or die ("Could not connect to $server. Please contact IT Servicedesk");

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, 'iisgnet\\' . $user, $pw);

				// verify binding
				if ($bd) {
					$login_correct = 1;
				}

				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

        if ( $login_correct == 0 ) {
            error_log("LOGIN FAILED $user from " . class_misc::get_remote_addr() . " (AD: " . trim(Settings::get('ms_active_directories')) . ")");
        }

		return $login_correct;
	}

	//
	public static function check_ldap($user, $pw, $authenticationServer) {
		global $dbConn;
		$login_correct = 0;

//preprint( 'knaw' );

		// gets ldap settings
		$query = "SELECT * FROM server_authorisation WHERE code = :code";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':code', $authenticationServer, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();

		$prefix = $result['prefix'];
		$postfix = $result['postfix'];
		$servers = $result['servers'];
		$protocol = $result['protocol'];

		// add prefix
		$user = $prefix . $user . $postfix;
		// remove double prefix
		$user = str_replace($prefix . $prefix, $prefix, $user); // voor alle zekerheid

		$activeDirectoryServers = unserialize($servers);

		// loop all Active Directory servers
		foreach ( $activeDirectoryServers as $server ) {
			if ( $login_correct == 0 ) {
//preprint( $protocol . $server['server'] . ":" . $server['port']);
//preprint( $user );
				// try to connect to the ldap server
				$ad = ldap_connect($protocol . $server['server'], $server['port']);

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding, if binding succeeds then login is correct
				if ($bd) {
					$login_correct = 1;
				} else {
					error_log("LOGIN FAILED $user from " . class_misc::get_remote_addr() . " (LDAP: " . $server . ", error: " . ldap_error() . ")");
				}

				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

//preprint( $login_correct );
//die('bbbb');

		return $login_correct;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
