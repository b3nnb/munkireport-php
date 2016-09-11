<?php

namespace controllers;

use munkireport\Controller as Controller;

/**
 * Module controller
 *
 * Loads the appropriate module, it looks a lot like the
 * engine class in kissmvc_core
 *
 * @package munkireport
 * @author AvB
 **/
class Module extends Controller
{
    public $module = 'default';
    public $action = 'index';

    public function __construct()
    {
    }

    public function index()
    {
    }
    
    
    public function load()
    {
        //Parse request (determine controller/action/params)
        $this->params = array();
        $p = func_get_args();
        if (isset($p[0]) && $p[0]) {
            $this->module=$p[0];
        }
        if (isset($p[1]) && $p[1]) {
            $this->action=$p[1];
        }
        if (isset($p[2])) {
            $this->params=array_slice($p, 2);
        }
                
        //Route request to correct controller/action
        $module_file = MODULE_PATH.$this->module.'/'.$this->module.'_controller.php'; //CONTROLLER CLASS FILE
        if (! preg_match('#^[A-Za-z0-9_-]+$#', $this->module) or ! file_exists($module_file)) {
            $this->requestNotFound('Module file not found: '.$module_file);
        }
        
        //Create module obj
        require($module_file);
        $this->module_classname = $this->module.'_controller';
        $class_to_instantiate = "modules\\$this->module\\$this->module_classname";

        if (! class_exists($class_to_instantiate, false)) {
            $this->requestNotFound('Module class not found: '.$this->module_classname);
        }
        $this->module_obj = new $class_to_instantiate;

        //call controller function
        if (! preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#', $this->action) or ! method_exists($this->module_obj, $this->action)) {
            $this->requestNotFound('Invalid function name: '.$this->action);
        }
        call_user_func_array(array( $this->module_obj, $this->action ), $this->params);
    }

    //Override this function for your own custom 404 page
    public function requestNotFound($msg = '')
    {
        header("HTTP/1.0 404 Not Found");
        die('<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>'.$msg.'<p>The requested URL was not found on this server.</p><p>Please go <a href="javascript: history.back( 1 )">back</a> and try again.</p><hr /><p>Powered By: <a href="http://kissmvc.com">KISSMVC</a></p></body></html>');
    }
}
