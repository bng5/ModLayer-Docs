<?php
class Skin extends Templates
{
	private $forceSkin   = false;
	private $forceDevice = false;
	private $loadExtra   = true;
	private $SkinPath    = true;
	private $baseXSL     = false;

	/* 
		Para compatibilidad con invocaciones en metodos viejos 
		y versiones anteriores
	*/
	public static function Load($baseXsl=false, $forceSkin=false, $forceDevice=false, $loadExtra=true)
	{
		$skin = new Skin($baseXsl, false);
		$skin->loadExtra   = $loadExtra;

		if($forceSkin)	
			$skin->forceSkin($forceSkin);

		if($forceDevice)
			$skin->forceDevice($forceDevice);
		
		$skin->Init();
		return $skin;
	}

	public function __construct($base=false, $init=true)
	{
		if($base)
			$this->baseXSL = $base;

		parent::__construct();

		$this->SetSkinPath();

		/* Load Config */
		$this->LoadConfig();

		if($init)
			$this->Init();
	}

	public function Init()
	{
		/* Load Stylesheets */
		$this->SetStylesheets();

		/* Si el usuario pidio cargar otra interfaz par su dispositivo la levantamos de la cookie */
		$this->SelectedDevice();

		/* Load Params */
		$this->SetParams();

		/* Load Debug */
		$this->SetDebug();

		/* Load Frontend user */
		$this->LoadFrontUser();

		/* Load extra data */
		$this->SetExtraData();
	}

	public function forceDevice($name)
	{
		$this->setDevice($name);
	}

	public function forceSkin($name)
	{
		$this->forceSkin = $name;
	}

	private function SelectedDevice()
	{
		$cookie = new Cookie();
		$device = $cookie->Read('selectdevice');

		if ( $device ) 
			$this->setDevice($device);
	}

	private function SetSkinPath()
	{
		$this->SkinPath = ($this->forceSkin) 
						? $this->forceSkin 
						: $this->GetDefault();

		$path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $this->SkinPath;
		$this->setpath($path);
	}

	/**
     * Return default skin path from configuration
    */
	private function GetDefault()
	{
		/**
		*	Multilenguage Support
		*/
		$lang = Session::Get('lang');

		if(!$lang)
		{
			$default = Configuration::Query("/configuration/skins/skin[@default='1']");
			
			/* We should have one default skin */
			if(!$default) 
				throw new Exception('Default Skin is not defined.');

			/* Default skin should have the language defined */
			$lang = $default->item(0)->getAttribute('lang');
			if(empty($lang))
				throw new Exception('Default Skin does not have a language defined.');
			
			Session::Set('lang', $lang);
		}

		// Util::debug($lang);
		$skin = Configuration::Query("/configuration/skins/skin[@lang='".$lang."']/path");

		/* If there is not a skin for the language stored, something is really wrong */
		if(!$skin){
			Session::Destroy('lang');	
			throw new Exception('Could not load the skin for language "' . $lang . '".');
		}

		$default = $skin->item(0)->nodeValue;
		$subdir = Configuration::Query('/configuration/domain/@subdir');
		if($subdir){
			$default = $subdir->item(0)->nodeValue . $default;
		}

		return $default;
	}

	/**
     * Add configuration data to transformation
     * 
     * @param string $base 
     */
	private function LoadConfig()
	{
		/* Client details */
		$details = $this->client->GetDetails();
		
		/* Skin Configuration */
		$skinconf = $this->GetConf();
		
		/* Not sensitive system configuration */
		$conf = Configuration::Query("/configuration/*[not(*)]");
		
		/* Load data to template */
		$this->setconfig($details, null, 'client');
		$this->setconfig($skinconf, '/skin', null);
		$this->setconfig($conf, null, 'system');
	}

	private function GetConf()
	{
		/* Skin Configuration path */
		return $this->getpath() . '/' . $this->device . '/skinconfiguration.xml';
	}

	/**
     * Add stylesheets to interface transformation
     * @param string $base 
     */
	private function setStylesheets()
	{
		$skinpath = $this->getpath();
		$confFile = $this->GetConf();;

		$d = new XMLDom();
		$d->load($confFile);
		$paths  = $d->query('/skin/pathinfo/path');
		$layout = $d->query("/skin/pathinfo/path[@baseStyleSheet='true']");

		if(!$layout)
			throw new Exception("No se encontró la ruta del XSL base en la configuración del Skin");

		if(!$this->baseXSL) {
			$this->setBaseStylesheet($skinpath . '/' . $this->device . $layout->item(0)->getAttribute('value'));
			if($paths){
				// Everything is included within the general interface
				foreach($paths as $path)
					$this->add($path->getAttribute('value'), $path = true);
			}
		} else {
			$this->setBaseStylesheet($skinpath . '/' . $this->device . '/xsl/' . $this->baseXSL);
			if($paths){
				foreach($paths as $path){
					// only requested items are included
					if($path->getAttribute('always') == 'true')
						$this->add($path->getAttribute('value'), $path = true);
				}
			}
		}
	}

	/**
     * Sets template params
    */
    private function SetParams()
    {
    	/* Requested url */
		$request = $_SERVER["REQUEST_URI"];
		if(strpos($request,'?')){
			$request = substr($request,0,strpos($request,'?'));
		}

		/* Active device loaded relative to root dir */
		$skinpath = $this->SkinPath . '/' . $this->device;

		/* Today's date */
		$date = date('Y-m-d');

		/* Token */
		// $token = Application::generateToken();
		
		/* Add params to template */
		$this->setparam("page_url", $request);
		$this->setparam('skinpath', $skinpath);
		$this->setparam('fechaActual', $date);
		// $this->setparam('modToken', $token);
    }

    /**
     * Add debug resources
    */
    private function SetDebug()
    {
    	$debug = Configuration::Query('/configuration/frontend_debug')->item(0)->nodeValue;

    	/* If debug is enabled add templates and param */
		if($debug == '1')
		{
			$skinpath  = $this->getpath();
			$debugFile = '/debug/debug.xsl';

			$this->add($debugFile, true);
			$this->debug = 1;
		}
    }

    /**
     * Add user to frontend xml
    */
    private function LoadFrontUser()
    {
    	/*
		* If User module is active and there's a 
		* frontend user logged add it
		*/
		try
		{
			if(class_exists("User", true))
			{
				// $userenabled = true;
				$u = new User();
				if ($user = $u->isLoguedIn()){
					$this->setconfig($user, null, 'user');
				}
			}
		}
		catch(Exception $e)
		{ 
			/* Could not load class */
			// echo $e->getMessage();
			// die;
		}
    }

    /**
    * Add Extra content to every page
    */
    private function SetExtraData()
    {
    	if(!$this->loadExtra) 
    		return false;

    	/*  Autoload from system conf.  */
		$syscontent = Configuration::Query('/configuration/autoload/content');
		$this->includeContent($syscontent);

		/*  Load extra content from rewrite rule  */
		$rule = Application::GetMatchedRule();
		if($rule) {
			$rulecontent = Configuration::Query('content', $rule);
			$this->includeContent($rulecontent);
		}
	}

	public function includeContent($nodes)
	{
		if($nodes)
		{
			foreach($nodes as $content)
			{
				$type = $content->getAttribute('type');
				switch($type)
				{
					case "localCall":
						$class  = $content->getAttribute('class');
						$method = $content->getAttribute('method');
						$params = Configuration::Query('arg', $content);
						$args   = array();
						$namedArgs = array();
						if($params){
							foreach ($params as $arg) {
								$args[$arg->getAttribute('name')] = $arg->getAttribute('value');
								$namedArgs['named_args'][$arg->getAttribute('name')] = $arg->getAttribute('value');
							}
						}
						$args = array_merge($args, $namedArgs);
						$autoload = call_user_func_array(array($class,$method), $args);
						break;
					case "localFile":
						$autoload = file_get_contents(PathManager::GetApplicationPath() . $content->getAttribute('file'));
						break;
					case "remoteFile":
						$autoload = $this->GetExternalFile($content);
						break;
				}
				if((bool)$content->getAttribute('json_decode') !== false)
				{
					$json     = json_decode($autoload);
					$autoload = $this->stdClassToArray($json);
				}
				if($autoload !== false){
					$this->setcontext($autoload, $content->getAttribute('xpath'), $content->getAttribute('placeholder'));
				}
			}
		}
	}

	private function stdClassToArray($d)
	{

		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(array('self', 'stdClassToArray'), $d);
		}
		else {
			// Return array
			return $d;
		}
	}

	/**
	*	Get the content of a remote file and writes
	*	a local copy (aka file cache). 
	*	Serve the local copy until expiration time 
	*	is reached
	*	
	*	@param  DOMElement $content 
	*	@return file contents
	*/
	private function GetExternalFile($content)
	{
		$ttl       = $content->getAttribute('ttl');
		$remoteUrl = Configuration::Query('remote', $content);
		$localpath = PathManager::GetApplicationPath() . $remoteUrl->item(0)->getAttribute('write');

		if(file_exists($localpath) && time() < filemtime($localpath) + $ttl)
		{
			$response = file_get_contents($localpath);
			return $response;
		}
		else
		{
			$url = $remoteUrl->item(0)->nodeValue;
			$url = str_replace('&amp;', '&', $url);
			// echo $url;
			// die;
			$filecontent = Util::GetDataFromUrl($url);
			
			if(!empty($filecontent)){
				$fp = fopen($localpath, 'w');
				fwrite($fp, $filecontent);
				fclose($fp);
				chmod($localpath, 0777);
			}
			return $filecontent;
		}
	}

}
?>