<?php
namespace reyzeal\Turnitin;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class FileDocument {
    public $id,$author,$title,$similarity,$download,$date;
    public function __construct(Session $session,$information){
        $this->session = $session;

        $this->id = $information[0];
        $this->author = $information[1];
        $this->title = $information[2];
        $this->similarity = $information[3];
        $this->download = $information[4];
        $this->date = $information[5];
    }

    public function download(){

    }

    public function report(){
        $response = $connection->client->request('GET',"https://ev.turnitin.com/app/carta/en_us/?lang=en_us&o=$oid",[
            'cookies' => $connection->cookiejar,
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $connection->session_id
            ]
        ]);
    }
}