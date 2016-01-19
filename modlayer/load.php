<?php

define('_ApplicationPath_', $_SERVER['DOCUMENT_ROOT']);

$php =  dirname(__FILE__) . '/core';
set_include_path($php);

/**
*	Register php files to autoload from classes directories
*/
spl_autoload_extensions('.php');
spl_autoload_register();

ini_set("memory_limit","128M");


/**
*	Set Error reporting
*/
error_reporting(E_STRICT | E_ALL);
ini_set('track_errors', 1); 

/* Set application path */
PathManager::SetApplicationPath(dirname(dirname(__FILE__)));

/**
*	Load Configuration
*/
Configuration::LoadConfiguration();

/**
*	We will handle Errors
*/
set_error_handler(array('Error', 'ErrorHandler'));
set_exception_handler(array('Error', 'ExceptionHandler'));

/**
*	default timezone should be in configuration
*/
date_default_timezone_set(Configuration::Query('/configuration/timezone')->item(0)->nodeValue);

/**
* Start Session
*/
session_set_cookie_params(0, '/', '', 0, 1);
ini_set('session.cookie_httponly', 1);
Session::Start();





?>