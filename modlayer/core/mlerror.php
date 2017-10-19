<?php 
	class MLError {


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

		Configuration::InitializeErrorReporting();

		$htmlError = self::BuildBackTrace($errorMessage, $backTrace, $file, $line, true);
		$textError = self::BuildBackTrace($errorMessage, $backTrace, $file, $line, false);

		$userReported = false;

		/*
			Mostrar el error en pantalla
		*/
		if (self::$_Screen)
		{
			if (!headers_sent())
			{
				header('Status: 503 Service Temporarily Unavailable');
				if (HTTPContext::Enabled())
				{
					echo $htmlError;
				}
				else
				{
					echo $textError;
				}
			}
			$userReported = true;
		}

		/*
			Envio del error por email
		*/
		if (self::$_Email)
		{
			try
			{
				$mails   = Configuration::GetEmails();
				$address = preg_split("/[;,]+/", $mails);
				$subject = 'Error - ' . Configuration::GetApplicationID();
				Application::SendEmail($address, $textError, $subject, $rtte=false);

			}
			catch (Exception $e){
				echo $e->GetMessage();
			}
		}
	
		
		/**
		* Redireccionar al error en pantalla al usuario final
		*/
		if (!$userReported)
		{
			Frontend::RenderError();
			// Util::redirect('/not-available/500/');
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
			$response = Frontend::DisplayInternalError($message, $backTrace, $fileName, $lineNumber, $htmlMode);
		}
		catch(Exception $e){

			echo $e->GetMessage();
			die;
		}
		return $response;
	}
	
	protected static function FindLastUserTrace($backTrace)
	{
		for (	$i = 0; 
				$i < count($backTrace) && 
					(!isset($backTrace[$i]['file']) ||
					strstr($backTrace[$i]['file'], PathManager::GetFrameworkPath()) !== false);
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

	public static function ShutdownHandler()
	{
		$errfile = "unknown file";
		$errstr  = "shutdown";
		$errno   = E_CORE_ERROR;
		$errline = 0;

		$error = error_get_last();

		// if( $error !== NULL) {
		// 	$errno   = $error["type"];
		// 	$errfile = $error["file"];
		// 	$errline = $error["line"];
		// 	$errstr  = $error["message"];

		// 	$trace = print_r( debug_backtrace( false ), true );

		// 	$content  = "<table><thead bgcolor='#c8c8c8'><th>Item</th><th>Description</th></thead><tbody>";
		// 	$content .= "<tr valign='top'><td><b>Error</b></td><td><pre>$errstr</pre></td></tr>";
		// 	$content .= "<tr valign='top'><td><b>Errno</b></td><td><pre>$errno</pre></td></tr>";
		// 	$content .= "<tr valign='top'><td><b>File</b></td><td>$errfile</td></tr>";
		// 	$content .= "<tr valign='top'><td><b>Line</b></td><td>$errline</td></tr>";
		// 	$content .= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
		// 	$content .= '</tbody></table>';

		// 	print($content);
		// }

		if( $error !== NULL) {

			$backTrace = debug_backtrace();


			// $errno   = $error["type"];
			$file         = $error["file"];
			$line         = $error["line"];
			$errorMessage = $error["message"] . '<br/>';
			$errorMessage .= 'File:' . $file . '<br/>';
			$errorMessage .= 'Line:' . $line . '<br/>';


			// $trace = print_r( debug_backtrace( false ), true );
			self::Report($errorMessage, $backTrace, $file, $line);
		}
	}
}
?>