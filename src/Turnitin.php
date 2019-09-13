<?php
namespace reyzeal;

class Turnitin{
    private $session;
    protected $sessionPath;
    public function __construct($username = null,$password = null,$forceToLogin = null,$sessionPath = null){
        if(!$username){
            if(function_exists('env')){
                $username = env('TURNITIN_USERNAME');
                $username = env('TURNITIN_PASSWORD');
            }
        }
        if(!$forceToLogin){
            if(function_exists('env')){
                $forceToLogin = env('TURNITIN_FORCELOGIN');
            }else{
                $forceToLogin = false;
            }
        }
        if(!$sessionPath){
            if(function_exists('env')){
                $sessionPath = env('TURNITIN_SESSION');
            }
            else{
                $sessionPath = __DIR__ . '/../../storage';
            }
        }
        $this->sessionPath = $sessionPath;
        $this->session = new Turnitin\Session($username,$password,$sessionPath,$forceToLogin);
    }

    public function classRoom(){
        return (new Turnitin\ClassRoom($this->session));
    }
    public function session(){
        return $this->session;
    }
}