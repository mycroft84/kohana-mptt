<?php

class Response_Core{
    
    private static $response=array();
    
    public static function get_response_headers($key)
    {
        return self::$response;
    }
    public static function clear_headers($header)
    {
        self::$response=array();
    }
    public static function add_header($header)
    {
        self::$response[]=$header;
        return true;
    }
    public static function send_headers()
    {
        foreach(self::$response as $response){
            header($response);
        }
    }
}
