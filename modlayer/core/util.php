<?php
class Util {
	/**
	*	This is a set of useful functions available to any module, 
	*	to simplify common tasks in php
	*/


	/**
	* Print an array.
	*
	* @param array $array the array to print.
	*
	* @return void.
	*/
	public static function debug($array)
	{
		echo "<pre>";
		print_r($array);
		echo "</pre>";
	}

	/**
	* Print an xml.
	*
	* @param DOMNodeList or DOMElement $m the object to be printed.
	*
	* @return void.
	*/
	public static function debugXML($m)
	{
		$out  = new DOMDocument();
		$root = $out->createElement('debugxml');
		switch(get_class($m))
		{
			case "DOMNodeList":
				foreach($m as $domElement)
				{
					$dom_sxe = $out->importNode($domElement, true);
					$root->appendChild($dom_sxe);
				}	
				break;
			case "DOMElement":
				$dom_sxe = $out->importNode($m, true);
				$root->appendChild($dom_sxe);
				break;
			case "XMLDom":
				$dom_sxe = $out->importNode($m->firstChild, true);
				$root->appendChild($dom_sxe);
				break;
			default:
				throw new Exception("Object type " . get_class($m) . ' can not be converted to xml.', 1);
				break;
		}
		$out->appendChild($root);

		header("Content-type:text/xml");
		echo $out->saveXML();		
	}
	
	/**
	* Redirect the user to given destination.
	* Calling die(); after changing header is required to prevent 
	* execution of extra code while the user is being redirected.
	* @param $location the destination to be redirected.
	* @return void.
	*/
	public static function redirect($location)
	{
		header('location:'.$location);
		die;
	}
	
	public static function getvalue($name, $default = false)
	{
		/*
			Always Return first the param from GET or POST 
		*/

		/* Return post parameter */
		if(isset($_POST[$name])) return $_POST[$name];

		/* Return get parameter */
		if(isset($_GET[$name]))  return $_GET[$name];

		/* Return named arg parameter */
		// Check if caller function is reciving the param
		$trace  = debug_backtrace();
		$search = self::SearchArrayByKey($trace, $name);

		if(isset($search[0]))    return $search[0];

		return $default;
	}

	/**
	* SearchArrayByKey
	* Search a multidimensional array for a given key name
	*/
	public static function SearchArrayByKey($array, $key)
	{
		$results = array();

		if (is_array($array))
		{
			if (isset($array[$key]))
				$results[] = $array[$key];

			foreach ($array as $sub_array)
				$results = array_merge($results, Util::SearchArrayByKey($sub_array, $key));
		}

		return  $results;
	}

	public static function getPost($name,$default=false)
	{
		return (isset($_POST[$name])) ? $_POST[$name] : $default;
	}
	
	public static function extend($defaults,$options)
	{
		$extended = $defaults;
		if(is_array($options) && count($options)) {
			$extended = array_merge($defaults,$options);
		}
		return $extended;
	}

	public static function getdaybydate($date){
		return date("l", mktime(0,0,0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4)));
	}

	public static function quote($string){
		$res = (get_magic_quotes_gpc())?stripslashes($string):$string;
		return "'".$res."'";
	}

	public static function isInteger($input){
	    return(ctype_digit(strval($input)));
	}

	/**
	* arrayNumeric will remove all elementos of an array with non-numeric keys
	* @param array() $arr the array to clear.
	* @return array the resulting array.
	*/
	public static function arrayNumeric($arr)
	{
		return array_intersect_key($arr, array_flip(array_filter(array_keys($arr), 'is_numeric')));
	}

	/**
	* Limpia la estructura de un array de modlayer en array a una forma mas simple 
	*/
	public static function ClearArray(&$input)
	{
		foreach ($input as $key => $value)
		{
			if (is_array($input[$key]))
			{
				self::ClearArray($input[$key]);
			}
			else
			{
				$value = str_replace("\n", '', $value);
				$value = str_replace("\r", '', $value);
				$value = str_replace("\n\r", '', $value);

				$saved_value = $value;
				$saved_key   = $key;
				self::ClearKeys($value, $key);

				if($value !== $saved_value || $saved_key !== $key):
					unset($input[$saved_key]);
					$input[$key] = strip_tags($value);
				endif;
			}
		}
	}

	public static function ClearKeys(&$value, &$key)
	{
		if(strpos($key, '-att') > 0)
		{
			$key = substr($key, 0, strlen($key) - strlen("-att"));
		}
		if(strpos($key, '-xml') > 0)
		{
			$key = substr($key, 0, strlen($key) - strlen("-xml"));
		}
	}

	public static function isAdmin()
	{
		$conf = Configuration::Query('/configuration/adminpath');
		$manager = $_SERVER['PHP_SELF']; /* Ruta del archivo ejecutado */
		$adminFolder = $conf->item(0)->nodeValue;

		if(stristr($manager, $adminFolder)):
			return true;
		else:
			return false;
		endif;
	}


	/**
	* Function used to create a slug associated to an "ugly" string.
	*
	* @param string $string the string to transform.
	*
	* @return string the resulting slug.
	*/
	public static function friendlyURL($string, $limit=false) 
	{

		$table = array(
		        'Š'=>'S', 'š'=>'s', 'Đ'=>'D', 'đ'=>'d', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
		        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
		        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' - ' => '-', ' ' => '-', '.' => '', ',' => '', '%' => '', 
		        '"' => '', "'" => '', ';' => '', '&' => '', '#' => '', ':' => '', '@' => '', '~' => '', 'º' => '',
		        '(' => '', ')' => '', '“' => '', '”' => '', '¿' => '', '?' => '', '—' => '', '!' => '', '¡' => '',
		        '$' => '', '¢' => '', '£' => '', 
		);

		// -- Remove duplicated spaces
		$stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);

		// -- Returns the slug
		$slug = strtolower(strtr($string, $table));

		if($limit) 
			$slug = substr($slug,0,$limit);

		return $slug;
	}

	public static function getDataFromURL($url, $data=false, $header=false)
	{

		$options = array(
			CURLOPT_RETURNTRANSFER => true,         // return web page
			CURLOPT_HEADER         => false,        // don't return headers
			CURLOPT_FOLLOWLOCATION => true,         // follow redirects
			CURLOPT_ENCODING       => "",           // handle all encodings
			CURLOPT_USERAGENT      => "modlayer-framework",     // who am i
			CURLOPT_AUTOREFERER    => true,         // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
			CURLOPT_TIMEOUT        => 120,          // timeout on response
			CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
			CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,        //
			CURLOPT_VERBOSE        => 1
		);
		if(is_array($header))
		{
			$options[CURLOPT_HTTPHEADER] = $header;
		}
		if(is_array($data))
		{
			$options[CURLOPT_POST]       = 1;
			$options[CURLOPT_POSTFIELDS] = $data;
		}

		$ch = curl_init($url); 
		curl_setopt_array($ch,$options);
		//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		$content = curl_exec($ch);
		$err     = curl_errno($ch);
		$errmsg  = curl_error($ch) ;
		$header  = curl_getinfo($ch);
		
		//$request_header_info = curl_getinfo($ch, CURLINFO_HEADER_OUT);
		curl_close($ch);
		
		//echo $request_header_info;
		return $content;
	}
	
	public static function OutputJson($data)
	{
		if(is_array($data)){$data = json_encode($data);}
		header("Content-Type: application/json; charset=UTF-8");
		echo $data;
		die;
	}

	public static function DirectorySeparator($path)
	{
		return preg_replace('#(\/|\\\)#', DIRECTORY_SEPARATOR, $path);
	}
}
?>