<?php
namespace reyzeal\Turnitin;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use reyzeal\Turnitin;

class ClassRoom extends Turnitin {
    protected $classList,$session;
    public $BYINDEX = 1;
    public $BYID = 2;
    public $BYNAME = 3;

    public function __construct(Session $session){
        $this->session = $session;
    }

    public function create($name,$enrollmentKey,$subjectArea,$studentLevel,$classEnd){

    }

    public function allRooms(){
        $client = new Client([
            'cookies' => $this->session->getCookies(),
        ]);
        $response = $client->request('GET','https://www.turnitin.com/t_home.asp',[
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $this->session->getId()
            ]
        ]);
        $html = $response->getBody();
        preg_match_all('/t_class_home.asp\?([^"]+)/',$html,$link);
        preg_match_all('/class_id">(\d+)/',$html,$classId);
        preg_match_all('/class_name"><a[^>]+>([^<]+)/',$html,$className);
        preg_match_all('/class_status">(\w+)/',$html,$classStatus);
        preg_match_all('/class_start_date">(\d+-\w+-\d+)/',$html,$classStart);
        preg_match_all('/class_end_date">(\d+-\w+-\d+)/',$html,$classEnd);
        $class = [];
        $i = 0;
        foreach ($link[0] as $detected){
            $replace1 = str_replace('&#61;','=',$detected);
            $replace2 = str_replace('&amp;','&',$replace1);
            $replace3 = str_replace('&%2361;','=',$replace2);

            $class[] = new SingleClass($this->session,[
                $classId[1][$i],
                $className[1][$i],
                $classStatus[1][$i],
                $classStart[1][$i],
                $classEnd[1][$i],
                $replace3,
            ]);
            $i++;
        }
        $this->classList = $class;
        return $class;
    }
    public function room($classIndex = 0){
        if(!$this->classList)
            $this->allRooms();
        return $this->classList[$classIndex];
    }
}