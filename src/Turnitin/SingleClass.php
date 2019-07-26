<?php
namespace reyzeal\Turnitin;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class SingleClass {
    public $id,$name,$status,$start,$end,$link,$assignments;
    private $session;
    public function __construct(Session $session,$class){
        $this->session  = $session;
        $this->id       = $class[0];
        $this->name     = $class[1];
        $this->status   = $class[2];
        $this->start    = $class[3];
        $this->end      = $class[4];
        $this->link     = $class[5];
    }

    public function allAssignment(){
        if($this->assignments) return $this->assignments;

        $client = new Client([
            'cookies' => $this->session->getCookies(),
        ]);
        $response = $client->request('GET','https://www.turnitin.com/'.$this->link,[
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $this->session->getId()
            ]
        ]);
        $html = $response->getBody();

        preg_match_all('/t_inbox.asp\?([^"]+)/',$html,$link);
        preg_match_all('/assgn-row" title="([^"]+)/',$html,$assignTitle);
        preg_match_all('/primary">(\d+-\w+-\d+)/',$html,$assignStart);
        preg_match_all('/secondary">(\d+:\d+\w+)/',$html,$assignEnd);
        $assignment = [];
        $i = 0;

        foreach ($link[0] as $detected){
            $replace1 = str_replace('&#61;','=',$detected);
            $replace2 = str_replace('&amp;','&',$replace1);
            $replace3 = str_replace('&%2361;','=',$replace2);

            $assignment[] = new Assignment($this->session,[
                $assignTitle[1][$i],
                $assignStart[1][$i],
                $assignEnd[1][$i],
                $replace3,
            ]);
            $i++;
        }
        $this->assignments = $assignment;
        return $assignment;
    }
    public function assignment($index = 0){
        if(!$this->assignments)
            $this->allAssignment();
        return $this->assignments[$index];
    }
}