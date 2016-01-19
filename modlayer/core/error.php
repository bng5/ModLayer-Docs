<?php
class Error
{


	protected static $_Email = false;
	protected static $_Screen = false;
	
	public static function SetEmail($email)
	{
		self::$_Email = $email;
	}
	
	public static function SetScreen($enabled)
	{
		self::$_Screen = $enabled;
	}
	
	protected static function Report($errorMessage, $backTrace, $file, $line)
	{

		$htmlError = self::BuildBackTrace($errorMessage, $backTrace, $file, $line, true);
		$textError = self::BuildBackTrace($errorMessage, $backTrace, $file, $line, false);


		
			header('Status: 503 Service Temporarily Unavailable');
			if (HTTPContext::Enabled())
			{
				echo $htmlError;
				die;
			}
			else
			{
				echo $textError;
			}
			
			$userReported = true;
		
	
		
		if (!$userReported)
		{
			/**
			* Redirect frontend user to generic error 
			*/

			Util::redirect('/error/500/');
		}

	}

	public static function Alert($message)
	{
		throw new Exception($message);
	}

	protected static function BuildBackTrace($message, array $backTrace, $fileName, $lineNumber, $htmlMode)
	{
		$response = '';
		try{
			$response = Skin::DisplayInternalError($message, $backTrace, $fileName, $lineNumber, $htmlMode);
		}
		catch(Exception $e){

			echo $e->GetMessage() . '<br/>';
			echo "<pre>";
			print_r($backTrace);
			echo "</pre>";
			die;
		}
		return $response;
	}
	
	protected static function FindLastUserTrace($backTrace)
	{
		for (	$i = 0; 
				$i < count($backTrace) && 
					(!isset($backTrace[$i]['file']) ||
					strstr($backTrace[$i]['file'], _ApplicationPath_) !== false);
				$i++);
		
		if ($i < count($backTrace))
		{
			return $backTrace[$i];
		}
		
		return null;
	}
	
	public static function ErrorHandler($errorNumber, $errorMessage, $fileName, $lineNumber, $variables)
	{
		if ($errorNumber & error_reporting())
		{
			$backTrace = debug_backtrace();

			unset($backTrace[0]['function']);
			unset($backTrace[0]['class']);
			unset($backTrace[0]['type']);
			
			$lastUserTrace = self::FindLastUserTrace($backTrace);

			if ($lastUserTrace)
			{
				$file = $lastUserTrace['file'];
				$line = $lastUserTrace['line'];
			}
			else
			{
				$file = null;
				$line = null;
			}
			self::Report($errorMessage, $backTrace, $file, $line);
		}
	}
	
	public static function ExceptionHandler($exception)
	{
		$backTrace = $exception->GetTrace();

		$lastUserTrace = self::FindLastUserTrace($exception->GetTrace());
		if ($lastUserTrace)
		{
			$file = $lastUserTrace['file'];
			$line = $lastUserTrace['line'];
		}
		else
		{
			$file = null;
			$line = null;
		}
		
		self::Report(get_class($exception) . '. '. $exception->GetMessage(), $backTrace, $file,	$line);
	}

}
?>