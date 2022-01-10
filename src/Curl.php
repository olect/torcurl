<?php

namespace Fucx\TorCurl;

use Fucx\TorCurl\Exception\ProxyNotExistException;
use Fucx\TorCurl\Exception\ProxyConnectionException;
use Fucx\TorCurl\Exception\TorCurlExecutionException;
use Fucx\TorCurl\Exception\NotImplementedException;
use Fucx\TorCurl\Exception\SingeltonException;
use Fucx\TorCurl\Exception\ProxyStartException;

class Curl {

	protected static $instance = null;
	private $handle = null;
	private $proxyPID = null;

	private $config;

	const REQUEST_METHOD_GET = 0;
	const REQUEST_METHOD_POST = 1;
	const REQUEST_METHOD_PUT = 2;
	const REQUEST_METHOD_DELETE = 3;
	const REQUEST_METHOD_PATCH = 4;

	const MAX_REDIRECTS = 5;
	const CON_TIMEOUT = 5;
	const TIMEOUT = 10;

	public function __construct($singelton = false, $url = null, ProxyConfig $proxyConfig) {

		if(!$singelton)
			throw new SingeltonException('Should not be constructed. Implements singelton pattern. Init through Curl::init method!');

		$this->handle = curl_init($url);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->handle, CURLOPT_HEADER, 0);
		curl_setopt($this->handle, CURLOPT_MAXREDIRS, self::MAX_REDIRECTS);
		curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, self::CON_TIMEOUT);
		curl_setopt($this->handle, CURLOPT_TIMEOUT, self::TIMEOUT);

		$this->setProxy($proxyConfig);
		
		if($proxyConfig->isDebug())
			curl_setopt($this->handle, CURLOPT_VERBOSE, 1);

		$this->config = $proxyConfig;

		$this->start();

		if(!$this->proxyExists($proxyConfig))
			throw new ProxyNotExistException();
	}

	private function start() {

		if($this->config->isDebug())
			echo 'Starting proxy. Please wait.';

		$this->startProxy();

		if(!$this->proxyHasProcess())
			throw new ProxyStartException('Problem starting proxy! No PID created.');

		while(!$this->proxyIsReady()) {
			if($this->config->isDebug())
				echo '.';
			sleep(2);
		}

		if($this->config->isDebug())
			echo "ready!\n";

		if($this->config->isDebug())
			echo 'Establishing identity: ';

		$this->getNewIdentity();

		if($this->config->isDebug())
			echo "Success\n";
	}

	public static function init($url = null, ProxyConfig $proxyConfig) {
		if(is_null(self::$instance))
			self::$instance = new Curl(true, $url, $proxyConfig);

		return self::$instance;
	}

	public function setHeaders(array $headers = []) {
		curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
	}

	public function setUserAgent($userAgent = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)') {
		curl_setopt($this->handle, CURLOPT_USERAGENT, $userAgent);
	}

	public function setCustomOption($curl_option_constant, $value) {
		if(isset($curl_option_constant) && !is_null($curl_option_constant) && isset($value) && !is_null($value))
			curl_setopt($this->handle, $curl_option_constant, $value);
	}

	public function getResponseCode() {
		return curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
	}

	private function proxyExists(ProxyConfig $proxyConfig) {
		return $proxyConfig->proxyExists();
	}

	private function setProxy(ProxyConfig $proxyConfig) {
		curl_setopt($this->handle, CURLOPT_PROXY, $proxyConfig->getConnectionString());
		curl_setopt($this->handle, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	}
	
	protected function execute($method = self::REQUEST_METHOD_GET, $url = null, $payload) {
		
		if(!is_null($url))
			curl_setopt($this->handle, CURLOPT_URL, $url);

		if(isset($payload)) {
			if($method == self::REQUEST_METHOD_GET) {
				if(!is_null($url) && is_array($payload) && count($payload) > 0) {
					curl_setopt($this->handle, CURLOPT_URL, $url . '?' .http_build_query($payload));
				}
			} elseif($method == self::REQUEST_METHOD_POST) {
				curl_setopt_array($this->handle,[
	                CURLOPT_POST => 1,
	                CURLOPT_POSTFIELDS => $payload,
	            ]);
			} else {
				throw new NotImplementedException('I was lazy, didn\'t care to implement this request method. Sue me :-p');
			}
		}

		$res = curl_exec($this->handle);

		if(curl_errno($this->handle))
			throw new TorCurlExecutionException(curl_error($this->handle), curl_errno($this->handle));

		return $res;
	}

	public function post($url = null, $args = []) {
		return $this->execute(self::REQUEST_METHOD_POST, $url, $args);
	}

	public function get($url = null, $args = []) {
		return $this->execute(self::REQUEST_METHOD_GET, $url, $args);
	}

	public function ignoreSSL() {
		curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER,false);
	}

	private function getNewIdentity() {
		$fp = fsockopen($this->config->getIp(), $this->config->getPort(), $err_number, $err_string, 10);
		if(!$fp) { 
			throw new ProxyConnectionException($err_string, $err_number);
		} else {
		    fwrite($fp,"AUTHENTICATE \"".$this->config->getPassword()."\"\n");
		    $received = fread($fp,512);
		    fwrite($fp, "signal NEWNYM\n");
		    $received = fread($fp,512);
		}
		 
		fclose($fp);
	}

	private function startProxy() {
		$this->proxyPID = shell_exec("nohup tor > /dev/null 2>&1 & echo $!");
	}

	private function proxyIsReady() {
		return $this->config->proxyExists();
	}

	private function stopProxy() {
		if($this->config->isDebug())
			echo "Stopping (killing) proxy process.\n";

		//shell_exec('kill ' . $this->proxyPID . '> /dev/null 2>&1'); // Doesn't work properly. Don't care to fix
		exec('killall tor');
		sleep(1); // Wait for proper kill
		unset($this->proxyPID);
	}

	private function proxyHasProcess() {
		exec("ps -p $this->proxyPID 2>&1", $state);
   		return (count($state) >= 2);
	}

	public function refreshIPAddress() {
		if($this->proxyHasProcess()) {
			$this->stopProxy();
		}
		
		$this->startProxy();

		if(!$this->proxyHasProcess())
			throw new ProxyStartException('Problem starting proxy! No PID created.');
		
		if($this->config->isDebug())
			echo 'Restarting proxy. Please wait.';

		while(!$this->proxyIsReady()) {
			if($this->config->isDebug())
				echo '.';
			sleep(2);
		}

		if($this->config->isDebug())
			echo "ready!\n";

		if($this->config->isDebug())
			echo 'Establishing identity: ';

		$this->getNewIdentity();

		if($this->config->isDebug())
			echo "Success\n";
	}

	public function close() {
		curl_close($this->handle);
	}

	public function __destruct() {
		$this->stopProxy();
		$this->close();
		unset($this->handle, $this->proxyPID, $this->config);
	}
}