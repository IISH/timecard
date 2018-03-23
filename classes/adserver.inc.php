<?php
class AdServerStatic {
	public static function getAdServers( $adServers ) {
		$ret = array();

		$arrAdServers = explode("\n", $adServers);
		foreach ( $arrAdServers as $adServer ) {
			$ret[] = new AdServer( $adServer );
		}

		return $ret;
	}
}

class AdServer {
	protected $protocol = '';
	protected $server = '';
	protected $port = '389';
	protected $isCorrect = 0;

	public function __construct( $server ) {
		// possible \r at end
		$server = trim($server);

		//
		$parts = explode("://", $server);
		switch ( count($parts) ) {
			case 1:
				$this->server = $parts[0];
				break;
			case 2:
				$this->protocol = $parts[0];
				$this->server = $parts[1];
				break;
		}

		//
		$parts = explode(":", $this->server);
		switch ( count($parts) ) {
			case 2:
				$this->server = $parts[0];
				$this->port = $parts[1];
				break;
		}
	}

	public function getProtocolAndServer() {
		$ret = $this->protocol;
		if ( $ret != '' ) {
			$ret .= '://';
		}
		$ret .= $this->server;

		return $ret;
	}

	public function getPort() {
		return $this->port;
	}

	public function isCorrect() {
		//
		if ( !(preg_match('/^[a-zA-Z]*$/', $this->protocol)) ) {
			return false;
		}

		//
		if ( !(preg_match('/^[0-9]*$/', $this->port)) ) {
			return false;
		}

		return true;
	}
}