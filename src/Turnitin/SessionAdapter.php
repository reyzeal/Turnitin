<?php
namespace reyzeal\Turnitin;

class SessionAdapter{
    protected $filename = '';
    protected function save($data){
        file_put_contents($this->filename,serialize($data));
    }
    protected function retrieve(){
        if(is_file($this->filename)){
            return unserialize(file_get_contents($this->filename));
        }else{
            return false;
        }
    }
}