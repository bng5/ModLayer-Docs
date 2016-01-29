<?php
Class FrontEnd {
	
	public static function DisplayDefault()
	{	
		$skin = Skin::Load();

		$version   = Application::Version();
		$tree      = Application::Tree($version);
		
		$home      = $version . '/home.xml';
		$nav       = $version . '/nav.xml';
		
		$skin->setcontent($home, '/xml/*', null);
		$skin->setcontext($tree, null, 'tree');
		$skin->setcontext(Application::GetVersions(), null, 'versions');
		$skin->setcontext($nav, '/nav', null);

		$skin->add('page.xsl');
		$skin->display();
	}

	public static function DisplayResource()
	{
		$url    = Util::getvalue('url');

		$url = $url . '.xml';
		self::DisplayPage($url);
	}

	public static function DisplayPage($xml)
	{
		$skin = Skin::Load();
		$version   = Application::Version();
		$nav       = $version . '/nav.xml';
		
		// Archivo con el contenido
		$xmlCont   = $version . '/' . $xml;

		if(!file_exists($xmlCont))
			Util::redirect('/not-found/404/?url=' . $xml);
		
		$skin->setcontent(
			$xmlCont, 
			'/xml/*', 
			null
		);
		$skin->setcontext(
			Application::GetVersions(), 
			null, 
			'versions'
		);
		$skin->setcontext(
			$nav, 
			'/nav', 
			null
		);
		$skin->add('page.xsl');

		self::output($skin);
		// $skin->display();
	}

	public static function Output(Templates $skin)
	{
		$format = Util::getvalue('format');

		switch($format){
			case "xml":
				$output = Skin::Load( 'output-xml.xsl');
				$output->setcontent($skin->returnXML(), '/xml/*', null);

				header("Content-Type: text/xml; charset=UTF-8");
				$output->display();				
				break;
			case "json":
				$response = Skin::Load( 'output-xml.xsl');
				$response->setcontent($skin->returnXML(), '/xml/*', null);

				$output   = Skin::Load( 'output-json.xsl');
				$output->setcontent($response->returnDisplay(), '/xml/*', null);

				header("Content-Type: application/json; charset=UTF-8");
				$output->display();
				break;
			default:
				$skin->display();
				break;
		}

		die;
		if($format == 'json')
		{
			
		}
		else
		{
			header("Content-Type: text/xml; charset=UTF-8");
			$skin->display();
			die;
		}
	}


	/*
		Not Found Page
	*/
	public static function FrontDisplayNotFound()
	{
		$skin = Skin::Load();
		$version   = Application::Version();
		$nav       = $version . '/nav.xml';
		
		$skin->setcontext($nav, '/nav', null);
		$skin->setparam("error", '404');
		$skin->add("core/error.xsl");
		header("Status: 404 Not Found");
		$skin->display();
	}

	/*
		Friendly Errors
	*/
	public static function FrontDisplayError()
	{
		libxml_clear_errors();
	
		$skin = Skin::Load($base=false, $active=false, $loadata=false);
		$skin->add("core/error.xsl");
		$skin->setparam("error", '500-100');
		header('Status: 503 Service Temporarily Unavailable');
		$skin->display();
	}

	/*
		Set Language
	*/
	public static function FrontSetLang()
	{
		$lang = Util::getvalue('lang');
		Application::SetLang($lang);
		Util::redirect('/');
	}

	/*
		Set Version
	*/
	public static function FrontSetVersion()
	{
		$ver = Util::getvalue('v');
		Application::SetVersion($ver);
		Util::redirect('/');
	}
	
}
?>