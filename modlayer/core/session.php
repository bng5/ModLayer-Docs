<?php

class Session {
    private static $started=false;


    public static function Start() {
    	HTTPContext::Start(true);
    }

    public static function Set($key, $value)
    {
        HTTPContext::Set($key, $value);
    }

   public static function Get($key)
   {
        return HTTPContext::Get($key);
    }

   public static  function Delete($key)
   {
        HTTPContext::Delete($key);
    }

    public static function Clean()
    {
        HTTPContext::Clean();
    }

    public static function Destroy()
    {
        HTTPContext::Destroy();
    }

    public static function Close()
    {
        HTTPContext::Close();
    }


}