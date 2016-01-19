<?php

class Device {

	protected $accept;
	protected $userAgent;
	protected $isMobile     = false;


	protected $devices = array(
		"LGTV"       => "NetCast.TV",
		"LGBDP"      => "NetCast.Media|NetCast-2010|BDP",
		"philips"    => "NETTV|PHILIPS_OLS",
		"samsung"    => "SmartHub|SMART-TV",
		"sony"       => "SONY|Trebuchet",
		"android"    => "android",
		"blackberry" => "blackberry",
		"iphone"     => "(iphone|ipod)",
		"ipad"       => "ipad",
		"opera"      => "opera mini",
		"palm"       => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
		"windows"    => "windows ce; (iemobile|ppc|smartphone)",
		"wget"       => "Wget",
		"generic"    => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)"
	);

	protected $OSList = array(
		'Win311'        => 'Win16',
		'Win95'         => '(Windows 95)|(Win95)|(Windows_95)',
		'WinME'         => '(Windows 98)|(Win 9x 4.90)|(Windows ME)',
		'Win98'         => '(Windows 98)|(Win98)',
		'Win2000'       => '(Windows NT 5.0)|(Windows 2000)',
		'WinXP'         => '(Windows NT 5.1)|(Windows XP)',
		'WinServer2003' => '(Windows NT 5.2)',
		'WinVista'      => '(Windows NT 6.0)',
		'Win7'          => '(Windows NT 6.1)',
		'WinNT'         => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
		'OpenBSD'       => 'OpenBSD',
		'SunOS'         => 'SunOS',
		'Linux'         => '(Linux)|(X11)',
		'MacOS'         => '(Mac_PowerPC)|(Macintosh)',
		'iOS'           => 'Mac OS X',
		'QNX'           => 'QNX',
		'BeOS'          => 'BeOS',
		'OS2'           => 'OS\/2',
		'SearchBot'     =>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves\/Teoma)|(ia_archiver)'
	);


	public function __construct()
	{
		$this->userAgent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$this->accept    = (isset($_SERVER['HTTP_ACCEPT']))? $_SERVER['HTTP_ACCEPT'] : '';

		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])|| isset($_SERVER['HTTP_PROFILE'])) {
			$this->isMobile = true;
		} elseif (strpos($this->accept,'text/vnd.wap.wml') > 0 || strpos($this->accept,'application/vnd.wap.xhtml+xml') > 0) {
			$this->isMobile = true;
		} else {
			foreach ($this->devices as $device => $regexp) {
				if ($this->isDevice($device)) {
					$this->isMobile = true;
				}
			}
		}
	}


	public function GetFolderName()
	{
		//$conf = Configuration::GetConfiguration();
		$devices = Configuration::Query('/configuration/devices/device');
		$generic = '';
		foreach($devices as $rule){
			$name = $rule->getAttribute('name');
			if($name != 'desktop'){
				$match = (bool) preg_match("/" . $this->devices[$name] . "/i", $this->userAgent);
				if($match){
					return $rule->getAttribute('directory');
					exit();
				}
			}
		}
		// return desktop
		$desktop = Configuration::Query('/configuration/devices/device[@default="1"]');
		return $desktop->item(0)->getAttribute('directory');
	}


	public function __call($name, $arguments)
	{
		$device = substr($name, 2);
		if ($name == "is" . ucfirst($device)) {
			return $this->isDevice(strtolower($device));
		} else {
			throw Exception("$name not defined", E_USER_ERROR);
		}
	}


	public function isMobile()
	{
		return $this->isMobile;
	}


	protected function isDevice($device)
	{
		$return = (bool) preg_match("/" . $this->devices[$device] . "/i", $this->userAgent);
		if ($device != 'generic' && $return == true) {
			$this->isGeneric = false;
		}
		return $return;
	}
	
	public function GetDeviceName()
	{
		foreach ($this->devices as $device => $regexp) {
			$return = (bool) preg_match("/" . $this->devices[$device] . "/i", $this->userAgent);
			if ($return) {
				$name = $device;
				break;
			}
		}
		if(empty($name)) $name='desktop';
		return $name;
	}
	
	protected function GetOsName()
	{
		foreach($this->OSList as $os => $regexp)
		{
			// Find a match
			if ((bool) preg_match("/" . $this->OSList[$os] . "/i", $this->userAgent)){
				
				return $os;
				break;
			}
    }
	}


	public function GetDetails()
	{
		$details = array();
		
		$details['name']       = $this->GetDeviceName();
		$details['os']         = $this->GetOsName();
		$details['user_agent'] = $this->userAgent;
		$details['browser']    = Browser::browser_detection('full_assoc');
		$details['tag']        = 'device';

		return $details;
	}





}?>