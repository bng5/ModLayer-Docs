<?php
class HTTPContext
{
	const SecurityUserKey = 'security.iduser';
	const SecurityKey = 'security';
	
	protected static $_Started = false;

	public static function Start($forceStart)
	{
		if (ini_get('session.auto_start'))
		{
			return;
		}
		
		$existingSession = isset($_COOKIE[session_name()]);

		if (isset($_COOKIE[session_name()]))
		{
			/**
			*	Prevent errors from illegal characters in session_id
			*/
			if (!preg_match('/^[a-z0-9\-]/', $_COOKIE[session_name()])) {
				unset($_COOKIE[session_name()]);
			}
		}

		if (!self::$_Started && ($existingSession || $forceStart))
		{
			session_start();
			/**
			 * Security hash to prevent fixation and hijacking.
			 */
			if (!$existingSession)
			{
				self::Reset();
			}
			else
			{
				$previousSecurityHash = isset($_SESSION[self::SecurityKey]) ? $_SESSION[self::SecurityKey] : null;
				if ($previousSecurityHash != self::CurrentSecurityHash())
				{
					self::Reset();
				}
			}
			
			self::$_Started = true;
		}
	}
	
	protected static function Reset()
	{
		session_regenerate_id(false);
		$_SESSION = array();
		$_SESSION[self::SecurityKey] = self::CurrentSecurityHash();
	}
	
	protected static function CurrentSecurityHash()
	{

		$extra = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'no-user-agent';
		return 	md5(Configuration::GetApplicationID() . $extra);
	}

	public static function Enabled()
	{
		return (PHP_SAPI == 'apache' || PHP_SAPI == 'apache2handler' || PHP_SAPI == 'cgi' || PHP_SAPI == 'cgi-fcgi');
	}

	public static function Get($key)
	{
		if (isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}
		return false;
	}

	public static function Set($key, $value)
	{
		self::Start(true);
		
		if (is_object($value) || is_resource($value))
		{
			throw new Exception('Only native types can be stored in the HTTP context (try Serialize).');
		}
		$_SESSION[$key] = $value;
	}

	public static function Delete($key)
	{
		// self::Start(true);
		
		if (isset($_SESSION[$key]))
		{
			unset($_SESSION[$key]);
		}
	}

	public static function Clean()
	{
		self::Start(true);
		self::Reset();
	}

	public static function Destroy()
	{
		self::Start(true);
		
		$_SESSION = array();
		
		if (!headers_sent() && isset($_COOKIE[session_name()]))
		{
			setcookie(session_name(), '', time() - 42000, '/', 1);
		}
		
		session_destroy();
	}

	public static function Close()
	{
		session_write_close();
	}
	
	public static function SetSecurityUser($idUser)
	{
		self::Set(self::SecurityUserKey, $idUser);
	}

	public static function GetSecurityUser()
	{
		return self::Get(self::SecurityUserKey);
	}
}
?>