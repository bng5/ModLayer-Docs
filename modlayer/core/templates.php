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

	public function setparam($name,$value)
	{
		$this->params[$name]=$value;
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
			Error::Alert("XML path not valid. <br/> $file");
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
		$this->setparam("debug", $this->debug);

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


	/**
	* @Add Method
	* Add a stylesheet from the current running module directory
	* @param file: name of the file to load
	**/
	public function add($templatename)
	{
		array_push($this->templates,$this->dirname.'/'.$this->device.'/xsl/'.$templatename);
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
			Error::Alert("Base XSL path not valid. <br/> $file");
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
			Error::Alert($message);
			die;
		}
	}
	
	
	public static function ValidateXSL($xsl)
	{
		if(!file_exists($xsl)){
			Error::Alert("XSL path not valid. <br/> $xsl");
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
			Error::Alert($message);
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
	
}
