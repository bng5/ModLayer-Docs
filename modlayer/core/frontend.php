<?php
Class Frontend {
	
	/*
		FrontSetLang setea el lenguage por default para frontend
	*/
	public static function SetLanguage()
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

		$action = $options['type'];
		// Generar la imagen
		$img = new Image();
		$img->load($source);
		$img->$action($options['width'], $options['height']);

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
		Util::redirect($redirect);
	}

	public static function desktopToMobile()
	{
		$redirect = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '/';

		setcookie('selectdevice', '', -1, '/', '', 0, 1);
		Util::redirect($redirect);
	}

	/**
	*	Deferred Publication
	*	
	*	@return void
	*/
	public static function deferredPublication()
	{
		try {
			$modules  = Configuration::Query('/configuration/modules/module');
		
			foreach($modules as $module)
			{
				if(method_exists($module->getAttribute('backend_controller'),'PublishContent'))
				{	
					$moduleController = $module->getAttribute('backend_controller');
					// Pregunta si el modulo no hereda de Element para no hacer tantas consultas a la base
					if (!is_subclass_of($moduleController, 'ElementController')){
						$moduleController::PublishContent();
					}
				}
			}
		}
		catch(Exception $e){
			echo $e->getMessage();
		}
		die;
	}


	/*
	*	Display System Error on screen
	*/
	public static function DisplayInternalError($message, $backTrace, $fileName, $lineNumber, $htmlMode=true)
	{
		$xsl = ($htmlMode) ? 'error.xsl' : 'error.text.xsl';
		// $xsl = 'error.xsl';
		libxml_clear_errors();

		$xslpath = PathManager::GetFrameworkPath() . '/error/' . $xsl;

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
		

		// Util::debug($backTrace);
		// die;
		$thisTemplate->setcontent($_SERVER, null, 'server');
		$thisTemplate->setcontent($backTrace, null, 'backtrace');
		$thisTemplate->setconfig($thisTemplate->client->GetDetails(), null, 'client');
		

		// $module    = Configuration::GetModuleConfiguration('admin');
		// 	$adminPath = substr(PathManager::GetFrameworkDir(),0, strlen(PathManager::GetFrameworkDir())) .  $module->getAttribute('path');

		
		// if($user = Admin::Logged()){
		// 	$thisTemplate->setcontent($user, null, 'user');
		// }

		
		$thisTemplate->setparam(
			array(
				'page_url'  => $request,
				'message'   => str_replace("'", '"', $message),
				// 'sqlquery'  => DBManager::$queryStr,
				'error'     => '500-100',
				'referer'   => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '',
			)
		);


		// $thisTemplate->AddStylesheet(PathManager::GetModuleAdminPath().'desktop/xsl/core/components.xsl');
		// $thisTemplate->AddStylesheet(PathManager::GetModuleAdminPath().'desktop/debug/debug.xsl');

		return $thisTemplate->returnDisplay();
	}

	public static function RenderNotFound()
	{
		$interface = Skin::Load();
		$interface->Status(404);
		$interface->setparam("error", '404');
		$interface->add("core/error.xsl");
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		$interface->display();
	}
	
	/*
	*	Display Friendly Error to frontend users
	*/
	public static function RenderError()
	{
		libxml_clear_errors();
	
		$skin = new Skin();
		$skin->Status(500);
		$skin->add("core/error.xsl");
		$skin->setparam("error", '500-100');
		$skin->display();

		// $interface = Skin::Load($baseXsl=false, $forceSkin=false, $forceDevice=false, $loadExtra=false);
		
		// $interface->add("core/error.xsl");
		// $interface->setparam("error", '500-100');
		// $interface->display();
	}

}
?>