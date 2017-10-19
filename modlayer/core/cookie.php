<?php
Class Cookie {

	
	protected $name = '__name-not-defined';
	protected $data;
	protected $time;
	protected $path;
	protected $domain;
	protected $secure;
	protected $httpOnly;
	protected $expires = 0;

	public function __construct($name=false, $params=false)
	{
		if($name) 
			$this->name = $name;

		$defaults = array(
			'path'     => '/',
			'domain'   => '',
			'time'     => 3600,
			'secure'   => 0,
			'httpOnly' => 1,
		);
		$options = Util::extend($defaults, $params);
		
		$this->path     = $options['path'];
		$this->domain   = $options['domain'];
		$this->time     = $options['time'];
		$this->secure   = $options['secure'];
		$this->httpOnly = $options['httpOnly'];
	}

	public function ttl($seconds)
	{
		$this->$time = $seconds;
	}

	public function Set($data)
	{
		$this->data = $data;
		$this->Put();
	}

	public function Put()
	{
		$cookie = base64_encode(
			serialize(
				$this->data
			)
		);
		setcookie($this->name, $cookie, time() + $this->time, $this->path, $this->domain, $this->secure, $this->httpOnly);
	}

	public function Read()
	{
		if(isset($_COOKIE[$this->name]))
		{
			$data = base64_decode($_COOKIE[$this->name]);
			return unserialize($data);
		}

		return false;
	}

	public function Drop()
	{
		setcookie($this->name, '', -1, $this->path, $this->domain, $this->secure, $this->httpOnly);
	}
}
?>