<?php
Class FrontEnd {
	
	public static function DisplayDefault()
	{	
		$interface = Skin::Load();

		$version   = Application::Version();
		$tree      = Application::Tree($version);
		
		$home      = $version . '/home.xml';
		$nav       = $version . '/nav.xml';
		
		$interface->setcontent($home, '/xml/*', null);
		$interface->setcontext($tree, null, 'tree');
		$interface->setcontext(Application::GetVersions(), null, 'versions');
		$interface->setcontext($nav, '/nav', null);

		$interface->add('page.xsl');
		$interface->display();
	}

	public static function DisplayResource()
	{
		$url = Util::getvalue('url');
		$url = $url . '.xml';

		self::DisplayPage($url);
	}

	public static function DisplayPage($xml)
	{
		$interface = Skin::Load();

		$version   = Application::Version();
		// $tree      = Application::Tree($version);
		
		$nav       = $version . '/nav.xml';
		
		// Archivo con el contenido
		$xmlCont   = $version . '/' . $xml;

		if(!file_exists($xmlCont))
			Util::redirect('/not-found/404/?url=' . $xml);
		
		$interface->setcontent($xmlCont, '/xml/*', null);
		// $interface->setcontext($tree, null, 'tree');
		$interface->setcontext(Application::GetVersions(), null, 'versions');
		$interface->setcontext($nav, '/nav', null);

		$interface->add('page.xsl');
		$interface->display();
	}


	/*
		Not Found Page
	*/
	public static function FrontDisplayNotFound()
	{
		$interface = Skin::Load();
		$version   = Application::Version();
		$nav       = $version . '/nav.xml';
		
		$interface->setcontext($nav, '/nav', null);
		$interface->setparam("error", '404');
		$interface->add("core/error.xsl");
		header("Status: 404 Not Found");
		$interface->display();
	}

	/*
		Friendly Errors
	*/
	public static function FrontDisplayError()
	{
		libxml_clear_errors();
	
		$interface = Skin::Load($base=false, $active=false, $loadata=false);
		$interface->add("core/error.xsl");
		$interface->setparam("error", '500-100');
		header('Status: 503 Service Temporarily Unavailable');
		$interface->display();
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