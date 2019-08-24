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
        $client = new Client([
            'cookies' => $this->session->getCookies(),
        ]);
//        $response = $client->request('GET',"https://ev.turnitin.com/app/carta/en_us/?lang=en_us&o=$this->id",[
//            'cookies' => $this->session->getCookies(),
//            'headers' => [
//                'accept-encoding' => 'gzip, deflate',
//                'session-id' => $this->session->getId()
//            ]
//        ]);
        $response = $client->request('GET',"https://www.turnitin.com/newreport_classic.asp?lang=en_us&oid=$this->id&ft=1&bypass_cv=1",[
            'cookies' => $this->session->getCookies(),
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $this->session->getId()
            ]
        ]);
        $data = $response->getBody();
        return $data;
    }
}