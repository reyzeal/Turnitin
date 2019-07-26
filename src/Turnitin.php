<?php
namespace reyzeal;

class Turnitin{
    private $session;
    protected $sessionPath;
    public function __construct($username,$password,$forceToLogin=false,$sessionPath = __DIR__ . '/../../storage'){
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