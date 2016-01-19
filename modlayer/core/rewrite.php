<?php

Class Rewrite
{

	public static function parseURL()
	{
		$rules = Configuration::Query('/configuration/rewrite/frontend/rule');
		if($rules)
		{
			$controller = 'FrontEnd';

			if(isset($rules->length))
			{
				foreach($rules as $rule)
				{
					self::MatchRule($rule, $controller);
				}
			}
		}

		
		$subject = $_SERVER['REQUEST_URI'];
		$domain = Configuration::Query('/configuration/domain')->item(0)->nodeValue;
		$subdir = Configuration::Query('/configuration/domain/@subdir');

		if($subdir)
		{
			$domain  = $domain . $subdir->item(0)->nodeValue;
			$subject = str_replace('/' . $subdir->item(0)->nodeValue, '', $subject); 
		}

		Util::redirect(rtrim($domain, '/') . '/not-found/404/?'.$subject);
	}

	public static function MatchRule($rule, $controller)
	{
		$subject = $_SERVER['REQUEST_URI'];
		$rulematch = $rule->getAttribute('match');

		/* Check if the app is running in a subdir */
		$subdir = Configuration::Query('/configuration/domain/@subdir');

		if($subdir && $subdir->item(0)->nodeValue != '')
		{
			$rulematch = '^\/' . $subdir->item(0)->nodeValue . $rulematch;
			// echo $rulematch;
			// die;
		}
		else{
			$rulematch = '^' . $rulematch;
		}


		$pattern = '#'.$rulematch.'#';
		$replace = ($rule->getAttribute('args')!='') ? $rule->getAttribute('args') : false;
		$method  = $rule->getAttribute('apply');


		if(preg_match($pattern, $subject, $matches))
		{
			if($replace)
			{
				$args = preg_replace($pattern, $replace, $subject);
				$args = str_replace('?','',$args);
				$args = explode('&', $args);

				$namedArgs = array();
				foreach($args as $argument)
				{
					$thisArg = explode('=', $argument);
					if(isset($thisArg[0]) && isset($thisArg[1])){
						$namedArgs[$thisArg[0]] = $thisArg[1];
					}
				}
				call_user_func_array(array((string)$controller,(string)$method), $namedArgs);
			}else{
				call_user_func(array((string)$controller,(string)$method));
			}
			die();
		}
	}
	
}

?>