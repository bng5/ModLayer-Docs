<?php
class Configuration
{
	protected static $_configuration = null;
	protected static $_configurationEnabled = null;
	protected static $dom;
	protected static $_ApplicationID = null;


	public static function ConfigurationEnabled()
	{
		if (!isset(self::$_configurationEnabled)){

			self::$_configurationEnabled = (self::GetConfigFile()) ? true : false;
		}
		return self::$_configurationEnabled;
	}
	
	public static function GetConfigFile()
	{
		$file = dirname(dirname(__FILE__)) . '/configuration.xml';
		if(file_exists($file) && XMLDoc::validate($file)){
			return $file;
		}else{
			die('Configuration File not found!');
		}
	}
	
	public static function GetApplicationID()
	{
		$query = self::Query('/configuration/applicationID');
		return $query->item(0)->nodeValue;
	}
	
	/* New configuration */

	public static function LoadConfiguration()
	{

		if(self::ConfigurationEnabled())
		{
			$file = self::GetConfigFile();
			$domDoc = new DOMDocument("1.0", "UTF-8");
			$domDoc->load($file);
			$domDoc->formatOutput = true;
			$modules = $domDoc->createElement('modules'); // This is where we'll insert each modules config.
			$domDoc->documentElement->appendChild($modules);
			self::$dom = $domDoc;
		}
	}
	
	public static function Query($node, $xml=false)
	{
		if(self::ConfigurationEnabled())
		{
			if($xml){
				$source = new DOMDocument("1.0", "UTF-8");
				$dom_sxe = $source->importNode($xml, true);
				$source->appendChild($dom_sxe);			
			}else{
				$source = self::$dom;
			}
			$conf   = new DOMXPath($source);
			$result = $conf->query($node);

			if($result->length == 0){
				return false;
			}else{
				return $result;
			}
		}
	}

}
?>