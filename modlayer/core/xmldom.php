<?php
Class XMLDom extends DOMDocument {
	
	private $dom;
	private $namespace = false;


	public function __construct()
	{
		parent::__construct('1.0', 'UTF-8');
		$this->preserveWhiteSpace = false;
		$this->formatOutput = true;
		return $this;
	}

	public function Query($node, $xml=false)
	{
		if($xml){
			$source = new XMLDom();
			$dom_sxe = $source->importNode($xml, true);
			$source->appendChild($dom_sxe);			
		}else{
			$source = $this;
		}

		$xpath  = new DOMXPath($source);

		if(is_array($this->namespace))
			$xpath->registerNamespace($this->namespace['name'], $this->namespace['value']);

		$return = $xpath->query($node);

		if(!$return)
			return false;
		elseif($return->length == 0)
			return false;
		else
			return $return;
	}

	public function registerNamespace($name, $value)
	{
		$this->namespace = array(
			'name' => $name,
			'value' => $value,
		);
	}
}
?>