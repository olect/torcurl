<?php

namespace Fucx\TorCurl;

class ProxyConfig {
	private $ip = '127.0.0.1';
	public function getIp() { return $this->ip; }

	private $port = '9050';
	public function getPort() { return $this->port; }

	private $password = 'PASSWORD';
	public function getPassword() { return $this->password; }

	private $debug = false;
	public function isDebug() { return $this->debug; }
	public function verbose() { $this->debug = true; }

	public function __construct($ip = null, $port = null, $password = null) {
		if(!is_null($ip))
			$this->ip = $ip;
		
		if(!is_null($port))
			$this->port = $port;
		
		if(!is_null($password))
			$this->password = $password;
	}

	public function getConnectionString() {
		return $this->ip . ':' . $this->port;
	}

	public function proxyExists() {
		$connection = @fsockopen($this->getIp(), $this->getPort());

    	if(is_resource($connection)) {
    		fclose($connection);
    		return true;
    	}

    	return false;
	}
}