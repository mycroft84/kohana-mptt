<?php

class Request_Core{
         
         public static $request_content = array(
        'js' => 'text/javascript',
        'css'   => 'text/css',
        'html'  => 'text/html',
        'form'  => 'application/x-www-form-urlencoded',
        'file'  => 'multipart/form-data',
        'xhtml' => array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
        'xml' => array('application/xml', 'text/xml'),
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml'
        );
        
        private static $accept_types = array();
        /**
         * init.  Parses the accepted content types accepted by the client using
         * HTTP_ACCEPT
         *
         * @access public
         * @return void
         */
        public function init() 
        {
            self::$accept_types = explode(',', self::env('HTTP_ACCEPT'));

            foreach ( self::$accept_types as $i => $type) 
            {
                if (strpos($type, ';')) 
                {
                    $type = explode(';', $type);
                     self::$accept_types[$i] = $type[0];
                }
            }        
        }
         function is_ajax() {
             if (self::env('HTTP_X_REQUESTED_WITH') != null) {
                 return self::env('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest";
             } else {
                 return false;
             }
         }
         function is_xhtml(){
             
             return self::accepts('xhtml');
         }
         /**
          * Returns true if the current call accepts an XML response, false otherwise
          *
          * @return bool True if client accepts an XML response
          * @access public
          */
         function is_xml() {
             return self::accepts('xml');
         }
         /**
          * Returns true if the current call accepts an RSS response, false otherwise
          *
          * @return bool True if client accepts an RSS response
          * @access public
          */
         function is_rss() {
             return self::accepts('rss');
         }
         /**
          * Returns true if the current call accepts an RSS response, false otherwise
          *
          * @return bool True if client accepts an RSS response
          * @access public
          */
         function is_atom() {
             return self::accepts('atom');
         }
         /**
          * Returns true if the current call is a POST request
          *
          * @return bool True if call is a POST
          * @access public
          */
         function is_post() {
             return (strtolower(self::env('REQUEST_METHOD')) == 'post');
         }
         /**
          * Returns true if the current call is a PUT request
          *
          * @return bool True if call is a PUT
          * @access public
          */
         function is_put() {
             return (strtolower(self::env('REQUEST_METHOD')) == 'put');
         }
         /**
          * Returns true if the current call is a GET request
          *
          * @return bool True if call is a GET
          * @access public
          */
         function is_get() {
             return (strtolower(self::env('REQUEST_METHOD')) == 'get');
         }
         /**
          * Returns true if the current call is a DELETE request
          *
          * @return bool True if call is a DELETE
          * @access public
          */
         function is_delete() {
             return (strtolower(self::env('REQUEST_METHOD')) == 'delete');
         }
                
    	/**
		 * From Cakephp
		 * Gets an environment variable from available sources.
		 * Used as a backup if $_SERVER/$_ENV are disabled.
		 *
		 * @param  string $key Environment variable name.
		 * @return string Environment variable setting.
		 */
         public function env($key) {
        
                if ($key == 'HTTPS') {
                    if (isset($_SERVER) && !empty($_SERVER)) {
                        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
                    } else {
                        return (strpos(env('SCRIPT_URI'), 'https://') === 0);
                    }
                }
        
                if (isset($_SERVER[$key])) {
                    return $_SERVER[$key];
                } elseif (isset($_ENV[$key])) {
                    return $_ENV[$key];
                } elseif (getenv($key) !== false) {
                    return getenv($key);
                }
        
                if ($key == 'SCRIPT_FILENAME' && defined('SERVER_IIS') && SERVER_IIS === true) {
                    return str_replace('\\\\', '\\', env('PATH_TRANSLATED') );
                }
        
                if ($key == 'DOCUMENT_ROOT') {
                    $offset = 0;
                    if (!strpos(env('SCRIPT_NAME'), '.php')) {
                        $offset = 4;
                    }
                    return substr(env('SCRIPT_FILENAME'), 0, strlen(env('SCRIPT_FILENAME')) - (strlen(env('SCRIPT_NAME')) + $offset));
                }
                if ($key == 'PHP_SELF') {
                    return r(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
                }
                return null;
            }  

         public function accepts($type = null) {
             if(empty(self::$accept_types))
                 self::init();
                 
            if ($type == null) {
                return self::$accept_types;
                
            } 
            elseif (is_array($type)) 
            {
                foreach ($type as $t) 
                {
                    if (self::accepts($t) == true) 
                    {
                        return true;
                    }
                }
                return false;
            } elseif (is_string($type)) 
            {
                // If client only accepts */*, then assume default HTML browser
                if ($type == 'html' && self::$accept_types === array('*/*')) 
                {
                    return true;
                }
    
                if (!in_array($type, array_keys(self::$accept_types))) 
                {
                    return false;
                }
    
                $content = self::$request_content[$type];
    
                if (is_array($content)) {
                    foreach ($content as $c) {
                        if (in_array($c, self::$accept_types)) {
                            return true;
                        }
                    }
                } else {
                    if (in_array($content, self::$accept_types)) {
                        return true;
                    }
                }
            }
        }   
        /*
         * 
         *     function setAjax(&$controller) {
        if ($this->isAjax()) {
            $controller->layout = $this->ajaxLayout;
            // Add UTF-8 header for IE6 on XPsp2 bug
            header ('Content-Type: text/html; charset=UTF-8');
        }
    }
         * 
         * 
         */
    
    }