<?php
class Skin
{
	/**
     * The template to be rendered
     * 
     * @var Templates
     */
    static $template;
  
	static $skinpath = '';

	public static function Load($baseXsl=false, $forceSkin=false, $forceDevice=false, $loadExtra=true)
	{
		self::$skinpath = ($forceSkin !== false) ? $forceSkin : Skin::GetDefault();
		
		self::$template = new Templates();

		/* Set Path to load XSL files */
		self::$template->setpath(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . self::$skinpath );

		if($forceDevice) self::$template->setDevice($forceDevice);

		/* Load Config */
		self::SetConfig();

		/* Load Stylesheets */
		self::SetStylesheets($baseXsl);

		/* Load Params */
		self::SetParams();

		/* Load Debug */
		self::SetDebug();

		/* Load Frontend user */
		self::LoadFrontUser();

		/* Load extra data */
		if($loadExtra !== false) self::SetExtraData();

		return self::$template;
	}

	/**
     * Return default skin path from configuration
    */
	private static function GetDefault()
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
				MLError::Alert('Default Skin is not defined.');

			/* Default skin should have the language defined */
			$lang = $default->item(0)->getAttribute('lang');
			if(empty($lang))
				MLError::Alert('Default Skin does not have a language defined.');
			
			Session::Set('lang', $lang);
		}

		// Util::debug($lang);
		$skin = Configuration::Query("/configuration/skins/skin[@lang='".$lang."']/path");

		/* If there is not a skin for the language stored, something is really wrong */
		if(!$skin){
			Session::Destroy('lang');	
			MLError::Alert('Could not load the skin for language "' . $lang . '".');
		}

		$default = $skin->item(0)->nodeValue;
		$subdir = Configuration::Query('/configuration/domain/@subdir');
		if($subdir){
			$default = '/' . $subdir->item(0)->nodeValue . $default;
		}

		return $default;
	}

	/**
     * Add configuration data to transformation
     * 
     * @param string $base 
     */
	private static function SetConfig()
	{
		/* Client details */
		$details = self::$template->client->GetDetails();
		
		/* Skin Configuration */
		$skinpath = self::$template->getpath();
		$skinconf = $skinpath . '/' . self::$template->device . '/skinconfiguration.xml';
		
		/* Not sensitive system configuration */
		$conf = Configuration::Query("/configuration/*[not(*)]");
		
		/* Load data to template */
		self::$template->setconfig($details, null, 'client');
		self::$template->setconfig($skinconf, '/skin', null);
		self::$template->setconfig($conf, null, 'system');
	}

	/**
     * Add stylesheets to template
     * 
     * @param string $base 
     */
	private static function setStylesheets($base)
	{
		$skinpath = self::$template->getpath();

		if(!$base){
			self::$template->setBaseStylesheet($skinpath . '/' . self::$template->device . '/xsl/core/layout.xsl');
			self::$template->add('core/header.xsl');
			self::$template->add('core/footer.xsl');
		}else{
			self::$template->setBaseStylesheet($skinpath . '/' . self::$template->device . '/xsl/' . $base);
		}

		/* Generic templates for all pages */
		self::$template->add('core/components.xsl');
		self::$template->add('core/templates.xsl');
	}

	/**
     * Sets template params
    */
    private static function SetParams()
    {
    	/* Requested url */
		$request = $_SERVER["REQUEST_URI"];
		if(strpos($request,'?')){
			$request = substr($request,0,strpos($request,'?'));
		}

		/* Active device loaded relative to root dir */
		$skinpath = self::$skinpath . '/' . self::$template->device;

		/* Today's date */
		$date = date('Y-m-d');
		
		/* Add params to template */
		self::$template->setparam("page_url", $request);
		self::$template->setparam('skinpath', $skinpath);
		self::$template->setparam('fechaActual', $date);
		
    }

    /**
     * Add debug resources
    */
    private static function SetDebug()
    {
    	$debug = Configuration::Query('/configuration/frontend_debug')->item(0)->nodeValue;

    	/* If debug is enabled add templates and param */
		if($debug == '1')
		{
			$path = PathManager::GetApplicationPath() . '/modlayer/debug/debug.xsl';
			$path = Util::DirectorySeparator($path);

			self::$template->addStylesheet($path);
			self::$template->debug = 1;
		}
    }

    /**
    * Add Extra content to every page
	* as detailed in configuration
    */
    private static function SetExtraData()
    {
		$extraContent = Configuration::Query('/configuration/autoload/content');
		if($extraContent)
		{
			foreach($extraContent as $content)
			{
				$type = $content->getAttribute('type');
				switch($type)
				{
					case "localCall":
						$class  = $content->getAttribute('class');
						$method = $content->getAttribute('method');
						$params = Configuration::Query('arg', $content);
						$args   = array();
						if($params){
							foreach ($params as $arg) {
								$args[$arg->getAttribute('name')] = $arg->getAttribute('value');
							}
						}
						$autoload = call_user_func_array(array($class,$method), $args);
						break;
					case "localFile":
						$autoload = file_get_contents(PathManager::GetApplicationPath() . $content->getAttribute('file'));
						break;
					case "remoteFile":
						$autoload = self::GetExternalFile($content);
						break;
				}
				if((bool)$content->getAttribute('json_decode') !== false)
				{
					$json     = json_decode($autoload);
					$autoload = self::stdClassToArray($json);
				}
				if($autoload !== false){
					self::$template->setcontext($autoload, $content->getAttribute('xpath'), $content->getAttribute('placeholder'));
				}
			}
		}
		// Util::debugXML($extraContent);
		// die;
	}

	private static function stdClassToArray($d)
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
	private static function GetExternalFile($content)
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

    private static function LoadFrontUser()
    {
    	/*
		* If User module is active and there's a 
		* frontend user logged add it
		*/
		$userenabled = false;
		try
		{
			if(class_exists("User", true))
			{
				// $userenabled = true;
				$u = new User();
				if ($user = $u->isLoguedIn()){
					self::$template->setconfig($user, null, 'user');
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


	/*
	*	Display System Error on screen
	*/
	public static function DisplayInternalError($message, $backTrace, $fileName, $lineNumber, $htmlMode=true)
	{
		$xsl = 'error.xsl';
		libxml_clear_errors();

		$xslpath = PathManager::GetApplicationPath() . '/modlayer/error/' . $xsl;

		$thisTemplate = new Templates();

		$thisTemplate->setErrorsheet($xslpath);
		$thisTemplate->ShowingError = true;
		
		$backTrace['get_params']  = $_GET;
		$backTrace['post_params'] = $_POST;
		$backTrace['tag'] = 'resource';
		

		$request = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "No url request";
		if(strpos($request,'?')){
			$request = substr($request,0,strpos($request,'?'));
		}
		$thisTemplate->setparam("page_url",$request);
		$thisTemplate->setparam('message', $message);

		/* Not sensitive system configuration */
		$conf = Configuration::Query("/configuration/*[not(*)]");
		$thisTemplate->setconfig($conf, null, 'system');

		$thisTemplate->setcontent($_SERVER, null, 'server');
		$thisTemplate->setcontent($backTrace, null, 'backtrace');
		$thisTemplate->setconfig($thisTemplate->client->GetDetails(), null, 'client');
		$thisTemplate->setparam('referer', (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '');

		$thisTemplate->setparam("error", '500-100');

		
		return $thisTemplate->returnDisplay();
	}

	public static function FrontDisplayNotFound()
	{
		$interface = Skin::Load();
		$interface->Status(404);
		$interface->setparam("error", '404');
		$interface->add("core/error.xsl");
		$interface->display();
	}
	
	/*
	*	Display Friendly Error to frontend users
	*/
	public static function FrontDisplayError()
	{
		libxml_clear_errors();
	
		$interface = Skin::Load($baseXsl=false, $forceSkin=false, $forceDevice=false, $loadExtra=false);
		$interface->Status(500);
		$interface->add("core/error.xsl");
		$interface->setparam("error", '500-100');
		$interface->display();
	}

	public static function FrontSetLang()
	{
		$lang = Util::getvalue('lang');
		Application::SetLang($lang);
		Util::redirect('/');
	}

	/**
	*	Proccess Image
	*	Generate a image the first time is requested to be served by apache later
	*	To take advantage of this service, you should use Apache Header module width Header set Cache-Control
	*	@return void
	*/
	public static function ProcessImage()
	{
		$id   = Util::getvalue('id');
		$args = Util::getvalue('params');
		$ext  = Util::getvalue('ext');
		
		$options = array(
			'id'     => $id,
			'width'  => false,
			'height' => false,
			'quality' => false,
			'type'   => 'resize',
		);

		// Parametro Ancho
		if(preg_match('/w([0-9]+)/i', $args, $outputWidth))	 $options['width'] = $outputWidth[1];

		// Parametro Alto
		if(preg_match('/h([0-9]+)/i', $args, $outputHeight)) $options['height'] = $outputHeight[1];

		// Parametro calidad
		if(preg_match('/q(100|\d{1,2})/i', $args, $outputHeight)) $options['quality'] = $outputHeight[1];

		// Type Crop / Resize
		$arr = explode('.', $args);
		if(strpos($arr[0], 'c') !== false) $options['type'] = 'crop';

		// Extension del archivo solicitado 
		$fileType = ($ext) ? strtolower($ext) : 'jpg';
		$fileType = substr($fileType, 0, 3);
		$fileType = ($fileType == 'jpe') ? 'jpg' : $fileType;
		$fileType = '.' . $fileType;

		// Ruta del a imagen origina en disco
		$sourceOpt = array(
			'module'       => 'image',
			'folderoption' => 'target'
		);

		$sourceDir  = PathManager::GetContentTargetPath($sourceOpt);
		$sourcePath = PathManager::GetDirectoryFromId($sourceDir, $id);
		$source = $sourcePath . '/' . $id . $fileType;


		$imageDir  = PathManager::GetApplicationPath() . Configuration::Query('/configuration/images_bucket')->item(0)->nodeValue;
		if (!is_dir($imageDir)){mkdir($imageDir, 0777);}
		$imagePath = PathManager::GetDirectoryFromId($imageDir, $id);
		$image     = $imagePath . '/' . $id;

		// El nombre del archivo contendrá los parametros para ser servida de manera estatica
		if($options['width'])  $image .= 'w' . $options['width'];
		if($options['height']) $image .= 'h' . $options['height'];
		if($options['quality'] !== false) $image .= 'q' . $options['quality'];
		if($options['type'] == 'crop') $image .= 'c';

		if(!file_exists($source)){
			$source = PathManager::GetApplicationPath() . '/content/not-found' . $fileType;
		}
		
		list($sourceWidth, $sourceHeight) = getimagesize($source);

		/* 
			Si no esta definido el ancho o el alto
			debemos asignar limites por defecto para la generación de la imagen
		*/
		$options['width']  = ($options['width']) ? $options['width'] : $sourceWidth;
		$options['height'] = ($options['height']) ? $options['height'] : $sourceHeight;

		
		
		// Generar la imagen
		$img = new Image();
		$img->load($source);
		$img->$options['type']($options['width'], $options['height']);

		/*
			Guardar la imagen en disco
			el próximo pedido será servido estáticamente por apache
		*/
		$quality = ($options['quality'] !== false) ? $options['quality'] : '80';
		$img->save($image, $quality);

		/* Mostrar la imagen */
		$img->display();
	}


	public static function mobileToDesktop()
	{
		$expires  = time()+60*60*24*90; // 90 dias
		$redirect = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '/';

		setcookie('selectdevice', 'desktop', $expires, '/', '', 0, 1);
		header('Location: ' . $redirect);
	}

	public static function desktopToMobile()
	{
		$redirect = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '/';

		setcookie('selectdevice', '', -1, '/', '', 0, 1);
		header('Location: ' . $redirect);
	}

	
}
?>