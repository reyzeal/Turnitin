<?php
namespace reyzeal\Turnitin;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class SessionAdapter{
    private $filesystem ;
    protected $filename = '';
    public function __construct($path){
        $adapter = new Local($path);
        $this->filesystem = new Filesystem($adapter);
    }
    protected function save($data){
        $this->filesystem->put($this->filename,serialize($data));
    }
    protected function retrieve(){
        if($this->filesystem->has($this->filename)){
            return unserialize($this->filesystem->read($this->filename));
        }
    }
}