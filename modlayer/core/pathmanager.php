<?php
final class PathManager
{
	private static $_Paths = array();
	private static $_ApplicationPath;
	private static $_TemporaryPath = null;
	

	public static function GetFrameworkPath()
	{
		return self::$_ApplicationPath . '/modlayer';
	}
	
	public static function AddClassPath($path)
	{
		array_unshift(self::$_Paths, $path);
	}

	public static function Apply()
	{
		set_include_path(implode(PATH_SEPARATOR, self::$_Paths));
	}

	public static function GetModuleAdminPath()
	{
		// retorno el path del modulo admin
		return self::GetModulesPath().'/admin/';
	}

	public static function GetApplicationPath()
	{
		return self::$_ApplicationPath;
	}

	
	public static function SetApplicationPath($path)
	{
		self::$_ApplicationPath = realpath($path);
	}

	public static function GetDirectoryFromId($directory, $id)
	{
		$folder = $directory.'/'. substr($id, - 1);
		$folder = Util::DirectorySeparator($folder);
		if (!is_dir($folder)){mkdir($folder, 0777);}
		return $folder;
	}

	/**
	*	FilePath : retorna la ruta del xml o json para un contenido por ID
	*	@param $id del elemento
	*	@param $json : flag para retornar la extension json. xml por default
	*	@return $path
	*/
	public static function FilePath($id, $module, $json = false)
	{
		$option = ($json) ? 'json' : 'xml'; 
		$options = array(
			'module'       => $module,
			'folderoption' => $option,
		);

		$path   = PathManager::GetContentTargetPath($options);
		$folder = PathManager::GetDirectoryFromId($path, $id);
		return Util::DirectorySeparator($folder.'/'.$id.'.'.$option);
	}
}
?>