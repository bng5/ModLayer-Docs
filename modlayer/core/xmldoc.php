<?php
class XmlDoc
{
	/**
	*	@author: Claudio Romano Cherñac
	*	El funcionalidad de esta clase es pasar un array a xml.
	*	Para crear xml con un arbol muy complejo es necesario especificar el nombre del tag para vectores con indice numericos con el subindice 'tag'.
	*	EJ simple:
	*	array(
	*		'name'=>'Book Title',
	*		'pages'=>'530',
	*		'chapters'=>array(
	*			'0'=>'Title One',
	*			'1'=>'Title Two',
	*			'2'=>'Title Three',
	*			'tag'=>'chapter'
	*			)
	*	)
	*	The result is:
	*	<xml>
	*		<name>Book Title</name>
	*		<pages>530</pages>
	*		<chapters>
	*			<chapter>Title One</chapter>
	*			<chapter>Title Two</chapter>
	*			<chapter>Title Three</chapter>
	*		</chapters>
	*	</xml>
	**/

	private $xml;
	private $encoding;
	private $context;
	private $config;
	private $content;
	private $destinyChild;

	public $data;
	public $dom_tree;
	public $dom_tree2;


	public	function __construct()
	{
		$this->data = new DOMDocument("1.0", "UTF-8");
		$this->data->formatOutput = true;
		$this->dom_tree = $this->data->createElement('xml');
		$this->config   = $this->data->createElement('configuration');
		$this->context  = $this->data->createElement('context');
		$this->content  = $this->data->createElement('content');
		$this->dom_tree->appendChild($this->config);
		$this->dom_tree->appendChild($this->content);
		$this->dom_tree->appendChild($this->context);

		$this->data->appendChild($this->dom_tree);
	}

	/* Generar un xml sin la estructura del sistema*/
	public function newXML($root)
	{
		$this->data->removeChild($this->dom_tree);
		$this->dom_tree2 = $this->data->createElement((string)$root);
		$this->data->appendChild($this->dom_tree2);
	}

	public function generateCustomXml($array){
		if(!is_array($array)):
			throw new Exception('XmlDoc require an array', 1);
			unset($this);
		endif;
		$this->recurse_node($array, $this->dom_tree2);
	}

	public function generateXML($config, $content, $context){
		foreach(func_get_args() as $arg):
			if(!is_array($arg)):
				throw new Exception('XmlDoc require an array', 1);
				unset($this);
			endif;
		endforeach;

		$this->recurse_node($config, $this->config);
		$this->recurse_node($content, $this->content);
		$this->recurse_node($context, $this->context);
	}


	public function addXml($insertIn, $file, $xpath=false, $destinyChild=false)
	{
		if(gettype($file) == 'object')
		{
			$this->importDOMObject($insertIn, $file, $xpath, $destinyChild);
		}
		else
		{

			$domDoc = new DOMDocument("1.0", "UTF-8");
			$domDoc->loadXML($file);
			$rootName = $domDoc->documentElement->tagName;

			// Add content to specified node
			if ($destinyChild && $destinyChild != $rootName){
				$node = $this->$insertIn->getElementsByTagName((string)$destinyChild);
				if($node->length == 0){
					//if node doesn't exist lets create it inside context
					$this->destinyChild = $this->data->createElement((string)$destinyChild);
					$this->$insertIn->appendChild($this->destinyChild);
				}else{
					//Otherwise we use the created node
					$this->destinyChild = $this->$insertIn->getElementsByTagName((string)$destinyChild)->item(0);
				}
			}

			$xp = new DOMXPath($domDoc);
			if($xpath){
				// Grab the node indicated
				$selectedNodes = $xp->query((string)$xpath);
				
				foreach($selectedNodes as $tag) {
					$nodeImport = $this->data->importNode($tag, true);
					if($destinyChild){
						// Insert it to specified node
						$this->destinyChild->appendChild($nodeImport);
					}else{
						// Otherwise insert it to context
						$this->$insertIn->appendChild($nodeImport);
					}
				}
			}else{
				// If no xpath is passed, grab the hole file
				$dom_sxe = $this->data->importNode($domDoc->documentElement, true);

				// If root node of xml is the same as Xpath, prevent duplicates.
				if($destinyChild == $rootName):
					$this->$insertIn->appendChild($dom_sxe);
				else:
					if($destinyChild){
						// Insert it to specified node
						$this->destinyChild->appendChild($dom_sxe);
					}else{
						// Otherwise insert into structure
						$this->$insertIn->appendChild($dom_sxe);
					}
				endif;
				//$this->$insertIn->appendChild($dom_sxe);
			}
		}
	}


	/**
	* array recursivo que devuelve el dom
	*
	* @param array $data
	* @param dom element $obj
	*/
	private function recurse_node($data, $obj){

		$i = 0;
		foreach($data as $key=>$value){

			// If is a numeric array use index 'tag' as node name, otheriwse default name is node{index}
			if (is_numeric($key)){
				$key = (isset($data['tag'])) ? $data['tag'] : 'node'.$key;
			}

			if(is_array($value))
			{
				$sub_obj[$i] = $this->data->createElement($key);
				$obj->appendChild($sub_obj[$i]);

				if($key != 'GLOBALS'){ // Globals is recursive and get errors.
					$this->recurse_node($value, $sub_obj[$i]);	
				}
			}
			elseif(is_object($value))
			{

				// This class does not support objects in array
				$sub_obj[$i] = $this->data->createElement($key, 'Object: "' . $key . '" type: "'  . get_class($value) . '"');
				$obj->appendChild($sub_obj[$i]);

			} else {

				$value = str_replace ('&' , '&amp;' , $value);

				if($key!='tag'){
					
					// Load as Attribute
					if(strrchr($key,'-') == '-att'){
						$key = substr($key,0,strlen($key)-strlen("-att"));
						$obj->setAttribute($key, $value);
					}
					
					// Load as XML content
					else if(strrchr($key,'-') == '-xml'){
						$key=substr($key,0,strlen($key)-strlen("-xml"));

						$nodeDoc = new DOMDocument("1.0", "UTF-8");
						$node = "<$key>".$value."</$key>";

						$this->validate($node);
						$nodeDoc->loadXML($node);

						$src = $nodeDoc->getElementsByTagName("$key")->item(0);
						$dom_nxe = $this->data->importNode($src, true);

						$sub_obj[$i] = $this->data->createElement($key);

						//$sub_obj[$i]->appendChild($dom_nxe);
						$obj->appendChild($dom_nxe);

					}

					// Load as Node
					else{
						// remove special characters from key
						$key = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $key);

						// Create element
						$sub_obj[$i] = $this->data->createElement($key, $value);
						$obj->appendChild($sub_obj[$i]);
					}
				}
			}
			$i++;
		}
	}

	/**
	 * Importa objetos DOMList y DOMElemet
	 *
	 * @param string $insertIn
     * @param object $file
     * @param string $xpath
     * @param string $destinyChild
     *
	 * @return void
	 */
	public function importDOMObject($insertIn, $file, $xpath, $destinyChild)
	{
		switch(get_class($file))
		{
			case "DOMNodeList":
				if($xpath)
				{
					$xp = new DOMXPath($file);
					$selectedNodes = $xp->query((string)$xpath);
					foreach($selectedNodes as $tag)
					{
						$this->importDOMElement($insertIn, $tag, $destinyChild);	
					}
				}
				else
				{
					foreach($file as $domElement)
					{
						$this->importDOMElement($insertIn, $domElement, $destinyChild);
					}	
				}
				break;
			case "DOMElement":
				if($xpath)
				{
					$xp = new DOMXPath($file);
					$selectedNodes = $xp->query((string)$xpath);
					foreach($selectedNodes as $tag)
					{
						$this->importDOMElement($insertIn, $tag, $destinyChild);	
					}
				}
				else
				{
					$this->importDOMElement($insertIn, $file, $destinyChild);
				}
				break;
			default:
				throw new Exception("Object type " . get_class($file) . ' can not be converted to xml.', 1);
				break;
		}
	}

	public function importDOMElement($insertIn, $file, $destinyChild)
	{
		if($destinyChild !== false && !is_null($destinyChild))
		{
			// if target node is already created, lets use it. 
			$node = $this->$insertIn->getElementsByTagName((string)$destinyChild);
			if($node->length == 0)
			{
				$this->destinyChild = $this->data->createElement((string)$destinyChild);
			}
			else
			{
				$this->destinyChild = $this->$insertIn->getElementsByTagName((string)$destinyChild)->item(0);
			}

			// Import DOMElement
			$nodeImport = $this->data->importNode($file, true);
			$this->destinyChild->appendChild($nodeImport);
			$this->$insertIn->appendChild($this->destinyChild);
		}
		else
		{
			$nodeImport = $this->data->importNode($file, true);
			$this->$insertIn->appendChild($nodeImport);
		}
	}


	/**
	 * Print this object as xml
	 *
	 * @return string
	 */
	public function saveXML(){
		return $this->data->saveXML();
	}
	
	
	public static function validate($xml)
	{
		if(is_file($xml)) $xml = file_get_contents($xml);
		libxml_use_internal_errors(true);
		$doc = new DOMDocument("1.0", "UTF-8");
		$doc->loadXML($xml);

		$errors = libxml_get_errors();
		if (empty($errors))
		{
			return true;
		}
		else
		{
			Util::debug($errors);
			die;
		}
	}
	

	public static function XMLTransform($xmlDoc, $xslDoc, $params=array())
	{
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->loadXML($xmlDoc);

		$xsl = new DOMDocument("1.0", "UTF-8");
		$xsl->loadXML($xslDoc);

		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);

		if (is_array($params) && count($params))
			{
				foreach ($params as $key=>$value) {
					$proc->setParameter(null, $key, $value);
				}
			}
		
		$fechaActual = date("Y-m-d");
		$horaActual  = date("H:i");
		$diaActual   = date("l", mktime(0,0,0, substr($fechaActual, 5, 2), substr($fechaActual, 8, 2), substr($fechaActual, 0, 4)));
		$proc->setParameter(null, "fechaActual", $fechaActual);
		$proc->setParameter(null, "horaActual", $horaActual);
		$proc->setParameter(null, "diaActual", $diaActual);
		
		return $proc->transformToXML($xml);
	}


	public static function UpdateXmlFragment($dom, $xpath, $value)
	{
		
		// Cargamos el dom document para queries
		$xmlTemp  = new DOMXPath($dom);

		$xmlString = explode('/', $xpath);
		$nodeName  = $xmlString[count($xmlString)-1];


		if(strpos($nodeName, '@') === false)
		{
			
			$old_node = $xmlTemp->query($xpath)->item(0);

			if(get_class($old_node) == 'DOMText'){
				$new_node = $dom->createTextNode($value);
			}else{
				$new_node = $dom->createElement($nodeName, $value);				
			}

			//$new_node = $dom->createElement($nodeName, $value);
			$old_node->parentNode->replaceChild($new_node, $old_node);
		}
		else
		{

			// Eliminamos el ultimo nodo que es el atributo
			array_pop($xmlString);


			// Volvemos a armar la ruta al nodo parent
			$parentNode = implode('/', $xmlString);

			// echo $parentNode , "<br/>";
			$old_node = $xmlTemp->query($parentNode)->item(0);

			// Eliminamos el atributo y lo volvemos a setear
			$nodeName = str_replace('@', '', $nodeName);
			if($old_node->hasAttribute($nodeName))
			{
				$old_node->removeAttribute($nodeName);	
			}
        	
        	$old_node->setAttribute($nodeName, $value);

		}
	}

}
?>