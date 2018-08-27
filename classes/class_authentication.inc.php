<?php
class Authentication {
	public static function authenticate( $login, $password ) {
		if ( strpos($login, '.') !== false ) {
			// if login contains 'dot' then IISG login
			return Authentication::check_ldap($login, $password, 'iisg');
		} else {
			// if login does not contain 'dot' then KNAW login
			return Authentication::check_ldap($login, $password, 'knaw');
		}
	}

	//
	public static function check_ldap($user, $pw, $authenticationServer) {
		$login_correct = 0;

		// get settings
		$auth = Authentication::getServerAuthorisationInfo($authenticationServer);

		//
		$user = trim($user);

		//
		$sAMAccountName = $user;

		// add prefix
		$user = $auth['prefix'] . $user;
		// remove double prefix
		$user = str_replace($auth['prefix'] . $auth['prefix'], $auth['prefix'], $user);

		// loop all Active Directory servers
		//foreach ( unserialize($auth['servers']) as $server ) {
		foreach ( AdServerStatic::getAdServers($auth['ad_servers']) as $server ) {
			if ( $login_correct == 0 ) {

				// try to connect to the ldap server
				$ad = ldap_connect($server->getProtocolAndServer(), $server->getPort());
				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				//$bd = @ldap_bind($ad, $user, $pw);
				$bd = ldap_bind($ad, $user, $pw);

				// verify binding, if binding succeeds then login is correct
				if ( $bd ) {
					$whitelist = trim($auth['whitelist']);
					$blacklist = trim($auth['blacklist']);

					if ( $whitelist == '' && $blacklist == '' ) {
						// there is no white/blacklist, so everyone who logged in via this server is okay
						$login_correct = 1; // authenticated & authorised
					} else {
						$result = ldap_search($ad, $auth['ldap_dn'], "(sAMAccountName=$sAMAccountName)", array("memberof")) or exit("Unable to search LDAP server");
						$entries = ldap_get_entries($ad, $result);

						// check whitelist
						if ( $whitelist == '' ) {
							// there is no whitelist, so everyone who logged in via this server is whitelisted
							$login_correct = 1; // authenticated & authorised
						} else {
							$login_correct = 2; // autheniticate but not authorised
							$whitelist = explode("\n", $whitelist);
							foreach ( $whitelist as $wl ) {
								foreach($entries[0]['memberof'] as $grps) {
									if ( $grps == trim($wl)) {
										$login_correct = 1; // authenticated & authorised
										break;
									}
								}
							}
						}

						// check blacklist
						if ( $login_correct == 1 ) {
							if ( $blacklist != '' ) {
								$blacklist = explode("\n", $blacklist);
								foreach ( $blacklist as $bl ) {
									foreach($entries[0]['memberof'] as $grps) {
										if ( $grps == trim($bl)) {
											$login_correct = 2; // authenticated but not authorised
//											preprint("222 AUTHORISATION FAILED $user from " . Misc::get_remote_addr() . " (LDAP: " . $server . ")");
											error_log("AUTHORISATION FAILED $user from " . Misc::get_remote_addr() . " (LDAP: " . $server . ")");
											break;
										}
									}
								}
							}
						}

					}
				} else {
					error_log("AUTHENTICATION FAILED $user from " . Misc::get_remote_addr() . " (LDAP: " . $server->getProtocolAndServer() . ")");
				}
				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

		return $login_correct;
	}

	public static function getServerAuthorisationInfo( $authenticationServer ) {
		global $dbConn;

		// get settings
		$query = "SELECT * FROM server_authorisation WHERE code = :code ";
		$stmt = $dbConn->prepare($query);
		$stmt->bindParam(':code', $authenticationServer, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();

		return $result;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
