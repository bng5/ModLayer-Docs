<?php
class Application {

	private static $_Paths = array();
	private static $_ApplicationPath;
	private static $_ApplicationID = null;
	private static $_frontend = false;
	private static $_module   = false;
	private static $_MatchedRule = false;

	
	
	
	public static function SetLang($lang)
	{
		$skins = Configuration::Query("/configuration/skins/skin");

		/* We should always have at least one skin */
		foreach($skins as $skin)
		{
			$skinlang = $skin->getAttribute('lang');

			/* if the parameter $lang is present */
			if($skinlang == $lang)
			{
				Session::Set('lang', $lang);
				return;
			}
		}

		echo 'No inteface defined for "' . $lang . '"';
		die;
	}

	public static function SetVersion($version)
	{
		$list = self::Source();

		/* We should always have at least one skin */
		foreach($list as $dir)
		{
			$parts  = explode('/', $dir);
			$dirVer = $parts[count($parts)-1];

			/* if the parameter $ver is present */
			if($version == $dirVer)
			{
				Session::Set('version', $dir);
				return;
			}
		}

		echo 'No version defined for "' . $version . '"';
		die;
	}

	public static function Source()
	{
		$lang = Session::Get('lang');
		$dir = PathManager::GetApplicationPath() . '/content/source/' . $lang . '/';
		$items  = array();
		$handle = opendir($dir);
		while (false !== ($item = readdir($handle))){
			if($item != '.' && $item != '..'){
				$path = $dir . $item;
				if(is_dir($path)){
					$items[] = $path;
				}
			}
		} // endwhile
		closedir($handle);
		return $items;
	}

	public static function Version()
	{
		/**
		*	Multi version Support
		*/
		$version = Session::Get('version');

		// If version is not set, take the last 
		if(!$version)
		{
			$list = self::Source();
			$version = end($list);
			Session::Set('version', $version);
		}
		return $version;
	}

	public static function Tree($version)
	{
		$data = self::GetFilesRecursive($version);
		return $data;
	}

	public static function GetVersions()
	{
		$list = self::Source();
		$data = array();
		foreach($list as $dir)
		{
			$parts  = explode('/', $dir);
			$dirVer = $parts[count($parts)-1];

			/* if the parameter $ver is present */
			$data[] = $dirVer;
		}
		$data['tag'] = 'version';
		return $data;
	}

	/*
		Send Email
	*/
	public static function SendEmail($address, $html, $subject, $rtte=false)
	{
		$mail = new PHPMailer(true);
		
		try {
			$mail->Host = Configuration::Query("/configuration/smtp/smtp_host")->item(0)->nodeValue; // SMTP server
			$mail->Port = Configuration::Query("/configuration/smtp/smtp_port")->item(0)->nodeValue; // Set the SMTP port
			
			$mail->CharSet  = 'UTF-8';

			$username = Configuration::Query("/configuration/smtp/smtp_user");
			$userpass = Configuration::Query("/configuration/smtp/smtp_pass");
			if($username && $userpass){
				$mail->IsSMTP(); // telling the class to use SMTP
				$mail->SMTPAuth = true;
				$mail->Username = $username->item(0)->nodeValue;
				$mail->Password = $userpass->item(0)->nodeValue;
			}

			if(is_array($address)) {
				foreach($address as $recipient) {
					$mail->AddAddress($recipient);
				}
			}
			else {
				$mail->AddAddress($address);
			}

			$rtte = ($rtte !== false) ? $rtte . ' ' : '';
			$rtte .= Configuration::GetSenderName();

			$mail->SetFrom(Configuration::GetSender(), $rtte);
			$mail->Subject = $subject;
			// $mail->MsgHTML(utf8_decode($html));
			$mail->MsgHTML($html);
			$mail->Send();
		}
		catch (Exception $e)
		{
			// echo $e->getMessage(); // El mensaje no se pudo enviar
		}
	}


	private static function GetFilesRecursive($directory)
	{
		$data = array();
		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}

		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory))
		{
			// ... we return false and exit the function
			return FALSE;

		// ... if the path is not readable
		}
		elseif(!is_readable($directory))
		{
			// ... we return false and exit the function
			return FALSE;

		// ... else if the path is readable
		}else{

			// we open the directory
			$handle = opendir($directory);

			// and scan through the items inside
			while (FALSE !== ($item = readdir($handle)))
			{
				// if the filepointer is not the current directory
				// or the parent directory
				if($item != '.' && $item != '..' && $item != '.DS_Store')
				{
					// we build the new path to delete
					$path = $directory.'/'.$item;

					// if the new path is a directory
					if(!is_dir($path)) 
					{
						$data[] = $item;
					}
					else
					{
						$data[$item] = self::GetFilesRecursive($path);
						
					}
					$data['tag'] = 'item';
				}
			}
			// close the directory
			closedir($handle);

			// return success
			return $data;
		}
	}
	

	/*
		La regla de rewrite matcheada se guarda localmente para 
		ser utilizada despues
	*/
	public static function SetMatchedRule($rule)
	{
		self::$_MatchedRule = $rule;
	}

	public static function GetMatchedRule()
	{
		return self::$_MatchedRule;
	}
}
?>