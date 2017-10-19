<?php
class Configuration
{
	protected static $_configuration = null;
	protected static $_configurationEnabled = null;
	protected static $dom;
	protected static $_ApplicationID = null;
	protected static $_ErrorReportingInitialized = false;


	public static function ConfigurationEnabled()
	{
		if (!isset(self::$_configurationEnabled)) {
			self::$_configurationEnabled = (self::GetConfigFile()) ? true : false;
		}
		return self::$_configurationEnabled;
	}
	
	public static function GetConfigFile()
	{
		$file = Util::DirectorySeparator(dirname(dirname(__FILE__)) . '/configuration.xml');
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
			$domDoc = new XMLDom();
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
				$source = new XMLDom();
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

	public static function InitializeErrorReporting()
	{
		if (!self::$_ErrorReportingInitialized)
		{

			if (!self::ConfigurationEnabled())
			{
				MLError::SetScreen(true, false);
			}
			else
			{

				$screen = self::Query('/configuration/errorReporting/screen');
				if ($screen && $screen->item(0)->getAttribute('enabled') == 'true')
				{
					MLError::SetScreen(true);
				}
				else
				{
					MLError::SetScreen(false);
				}
				
				$email = self::Query('/configuration/errorReporting/email');

				if ($email && $email->item(0)->getAttribute('enabled') == 'true')
				{
					MLError::SetEMail(true);
				}else{
					MLError::SetEMail(false);
				}

			}
			self::$_ErrorReportingInitialized = true;
		}
	}

	public static function GetEmails()
	{
		$destination = self::Query('/configuration/errorReporting/email');
		return $destination->item(0)->getAttribute('destination');
	}

	public static function GetSender()
	{
		$sender = self::Query('/configuration/errorReporting/email');
		return $sender->item(0)->getAttribute('sender');
	}
	
	public static function GetSenderName()
	{
		$sender = self::Query('/configuration/errorReporting/email');
		return $sender->item(0)->getAttribute('sendername');
	}

}
?>