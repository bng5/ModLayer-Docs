<?php

class Templates {
	
	private $templates=array();
	private $values=array();
	private $params=array();
	private $context=array();
	private $configuration=array();
	public  $device;
	public  $client;
	private $xmlDoc;
	private $xsl;
	private $templateXsl;
	private $dirname;
	public $debug = 0;
	public $ShowingError = false;
	public $statusCode = false;

	function __construct()
	{
		$this->xmlDoc = new XMLDoc();
		// Detect devide to load proper xsl
		$this->client = new Device();
		$this->device = $this->client->GetFolderName();
	}

	function __destruct(){}

	public function setDevice($name){$this->device = $name;}
	
	public function getDevice(){return $this->device;}

	public function Status($code) {
		$this->statusCode = $code;
	}

	public function setparam($name,$value=false)
	{
		if(is_array($name)) {
			array_walk($name, function(&$value, $key){
				$this->params[$key] = $value;
			});
		}
		else {
			$this->params[$name]=$value;
		}
	}

	function setcontext($path, $nodeSource=false, $destinyNode=false)
	{
		if(is_array($path)): 
			$this->context[$destinyNode] = $path; 
		else:
			if(gettype($path) != 'object' && is_file($path)){
				$string = $this->fileToString($path);
			}
			else{
				$this->ValidateXML($path);
				$string = $path;
			}
			$this->xmlDoc->addXml('context', $string, $nodeSource, $destinyNode);
		endif;
	}
	
	function setconfig($path, $nodeSource=false, $destinyNode=false)
	{
		if(is_array($path)): 
			$this->configuration[$destinyNode] = $path; 
		else:
			if(gettype($path) != 'object' && is_file($path)){
				$string = $this->fileToString($path);
			}
			else{
				$this->ValidateXML($path);
				$string = $path;
			}
			$this->xmlDoc->addXml('config', $string, $nodeSource, $destinyNode);
		endif;
	}
	
	public function setcontent($path, $nodeSource=false, $destinyNode=false)
	{
		if(is_array($path)): 
			$this->values[$destinyNode]=$path;
		elseif(!$path):
			$this->values[$destinyNode]='';
		else:
			if(gettype($path) != 'object' && is_file($path)){
				$string = $this->fileToString($path);
			}
			else{
				$this->ValidateXML($path);
				$string = $path;
			}
			$this->xmlDoc->addXml('content', $string, $nodeSource, $destinyNode);
		endif;
    }

	private function fileToString($file)
	{
		if(!file_exists($file))
		{
			MLError::Alert("XML path not valid. <br/> $file");
		}
		else
		{
			$data = @file_get_contents($file);
			$this->ValidateXML($data);
			return $data;	
		}
	}

	function setpath($path)
	{
		$this->dirname=$path;
	}
	
	function getpath()
	{
		return $this->dirname;
	}
	
	public function display()
	{
		$this->setparam(
			array(
				"debug" => $this->debug,
				'status' => $this->statusCode,
			)
		);

		$this->xmlDoc->generateXML($this->configuration, $this->values, $this->context);

		$this->importStylesheets();
		echo $this->xmlDoc->XMLTransform($this->xmlDoc->saveXML(),$this->xsl->saveXML(),$this->params);
		exit();
	}

	public function displayXML()
	{
		$this->xmlDoc->generateXML($this->configuration, $this->values, $this->context);
		header("content-type:text/xml");
		echo $this->xmlDoc->saveXML();
		exit();
	}

	public function displayXSL()
	{
		header("content-type:text/xml");
		$this->importStylesheets();
		echo $this->xsl->saveXML();
		exit();
	}
	
	public function returnDisplay()
	{
		$this->xmlDoc->generateXML($this->configuration, $this->values, $this->context);
		$this->importStylesheets();
		return $this->xmlDoc->XMLTransform($this->xmlDoc->saveXML(),$this->xsl->saveXML(),$this->params);
	}

	public function returnXML()
	{
		$this->xmlDoc->generateXML($this->configuration, $this->values, $this->context);
		$this->importStylesheets();
		return $this->xmlDoc->saveXML();
	}


	/**
	* @Add Method
	* Add a stylesheet from the current running module directory
	* @param file: name of the file to load
	**/
	public function add($templatename, $xslFolder=false)
	{
		$dir = (!$xslFolder) ? '/xsl/' : '';
		array_push( $this->templates , $this->dirname . '/' . $this->device . $dir . $templatename );
	}

	/**
	* @AddStylesheet Method
	* Add a stylesheet from any directory
	* @param file: full path to the file to add
	**/
	public function addStylesheet($file)
	{
		array_push($this->templates,$file);
	}

	/**
	* @setBaseStylesheet Method
	* Load core stylesheet. This stylesheet injected with 
	* params, variables and templates from other added stylesheets    
	* @param file: full path to the file to add
	**/
	public function setBaseStylesheet($file)
	{
		if(!file_exists($file)){
			MLError::Alert("Base XSL path not valid. <br/> $file");
		}
		$check = $this->ValidateXSL($file);
		$this->xsl = new DOMDocument('1.0', "UTF-8");
		$this->xsl->load($file);
	}

	/**
	* @importStylesheets Method
	* Parse added stylesheets to this class, and injects
	* params, variables and templates from each xsl to the base xsl.
	* Keep params and variables declarations above templates.
	**/
	public function importStylesheets()
	{
		foreach($this->templates as $template)
		{
			if(!$this->ShowingError)
			{
				$check = $this->ValidateXSL($template);
			}
			
			$localStylesheet = new DOMDocument('1.0', "UTF-8");
			$localStylesheet->formatOutput = true;
			$localStylesheet->load($template);

			$root   = new DOMXPath($this->xsl);
			$output = $root->query('xsl:output')->item(0);
			$xp     = new DOMXPath($localStylesheet);

			$nodes     = $xp->query('xsl:template');
			$params    = $xp->query('xsl:param');
			$variables = $xp->query('xsl:variable');

			// Include xsl:param nodes from all xsl stylesheets 
			foreach($params as $tag) {
				$dom_sxe = $this->xsl->importNode($tag, true);
				$output->parentNode->insertBefore($dom_sxe, $output->nextSibling);
			}

			// Include xsl:variables nodes from all xsl stylesheets
			foreach($variables as $tag) {
				$dom_sxe = $this->xsl->importNode($tag, true);
				$output->parentNode->insertBefore($dom_sxe, $output->nextSibling);
			}

			// Include xsl:template nodes from all xsl stylesheets
			foreach($nodes as $tag) {
				$dom_sxe = $this->xsl->importNode($tag, true);
				$this->xsl->documentElement->appendChild($dom_sxe);
			}
		}
		$this->templates=array();
	}



	/**
	* @ValidateXML Method
	* Check if a xml string is well formed
	**/
	public static function ValidateXML($xml, $file=false)
	{
		if(gettype($xml) == 'object'){
			if(get_class($xml) == 'DOMNodeList' || get_class($xml) == 'DOMElement')
			{
				return true;
			}
		}

		libxml_use_internal_errors(true);
		$doc = new DOMDocument('1.0', "UTF-8");
		$doc->loadXML($xml);
		$errors = libxml_get_errors();

		if (empty($errors)){
			return true;
		}else{
			$error = $errors[0];
			$lines = explode("r", $xml);
			if($error->line > 0){
				$line  = $lines[($error->line)-1];
			}else{
				$line  = $lines[0];
			}
			
			$message = "Error parsing xml file. <br/>";
			$message .= $error->message.' at line '.$error->line.':<br />'.htmlentities($line).'<br/>';
			if($file) $message .= "File: $file";
			MLError::Alert($message);
			die;
		}
	}
	
	
	public static function ValidateXSL($xsl)
	{
		if(!file_exists($xsl)){
			MLError::Alert("XSL path not valid. <br/> $xsl");
		}

		libxml_use_internal_errors(true);
		$doc = new DOMDocument('1.0', "UTF-8");
		$doc->load($xsl);
		$errors = libxml_get_errors();


		if (empty($errors))
		{
			return true;
		}
		else
		{
			$error = $errors[0];
			$message = "Error parsing xsl file. <br/>";
			$message .= $error->message.' at line '.$error->line.':<br />';
			$message .= "File: $xsl";
			MLError::Alert($message);
			die;
		}
	}

	public function setErrorsheet($file)
	{
		if(!file_exists($file)){
			echo "Base XSL path not valid. <br/> $file";
		}
		//$check = $this->ValidateXSL($file);
		$this->xsl = new DOMDocument('1.0', "UTF-8");
		$this->xsl->load($file);
	}

	public function StatusHeader()
	{
		$status_codes = array (
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended'
		);

		if ($this->statusCode !== false) {
			$status_string = $this->statusCode . ' ' . $status_codes[$this->statusCode];
			header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $this->statusCode);
		}
	}
	
}
