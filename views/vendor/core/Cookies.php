<?php

namespace Views\vendor\core;


class Cookies
{

    /*
     * активные куки здесь
     */
    protected static $cookies = [];

    public static function set($name, $value, $time=null, $path=null, $domain=null)
    {
        if ( empty($name) || empty($value) ) return false;
        if ( empty($time) || !is_int($time) ) $time = time()+3600; // час
        if ( setcookie($name, $value, $time, '/', $_SERVER['HTTP_HOST'] ) )
        {
            self::$cookies[$name] = $value;
            return true;
        }
        return false;
    }

    public static function dellAllCookies()
    {
        $result = [];
        foreach(self::getAll() as $cookie)
        {
            if ( setcookie($cookie, '', 1, '/', $_SERVER['HTTP_HOST']) )
            {
                $result[] = true;
            } else {
                $result[] = false;
            }
        }
        self::$cookies = [];
        foreach($result as $res)
        {
            if ( $res === false ) return false;
        }

        return true;
    }

    public static function dellOne()
    {

    }

    public static function getCookies()
    {
        return self::$cookies;
    }

    public static function getAll()
    {
        if ( !empty($_COOKIE) ) return $_COOKIE;
        return [];
    }

    public static function getOne($name)
    {
        if ( isset($_COOKIE[$name]) ) return $_COOKIE[$name];
        return false;
    }

}