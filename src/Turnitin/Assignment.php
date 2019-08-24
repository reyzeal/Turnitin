<?php
namespace reyzeal\Turnitin;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class Assignment {
    public $title,$start,$end,$link,$documents;
    private $session,$referer;
    public function __construct(Session $session,$information){
        $this->session = $session;
        $this->title = $information[0];
        $this->start = $information[1];
        $this->end = $information[2];
        $this->link = $information[3];
    }
    public function allDocuments(){
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
        preg_match_all('/pid">([^<]+)/',$html,$pid);
        preg_match_all('/ibox\_author "..([^<]+)/',$html,$author);
        preg_match_all('/paper_frame[^>]+>([^<]+)/',$html,$title);
        preg_match_all('/(download_format_select\.asp\?[^\']+)/',$html,$download);
        preg_match_all('/or-percentage">([^<]+)/',$html,$similarity);
        preg_match_all('/(\d+-\w+-\d+)/',$html,$date);


        preg_match_all('/(t_submit\.asp\?[^"]+)/',$html,$referer);
        $replace1 = str_replace('&#61;','=',$referer[1][0]);
        $replace2 = str_replace('&amp;','&',$replace1);
        $replace3 = str_replace('&%2361;','=',$replace2);
        $this->referer = $replace3;
        $documents = [];
        $i = 0;
        foreach ($pid[0] as $id){
            if(isset($similarity[1][$i])){
                $sim = $similarity[1][$i];
            }else{
                $sim = null;
            }
            $documents[] = new FileDocument($this->session,[
                $pid[1][$i],
                $author[1][$i],
                $title[1][$i],
                $sim,
                isset($download[1][$i])?$download[1][$i]:null,
                $date[1][$i],
            ]);
            $i++;
        }
        $this->documents = $documents;
        return $documents;
    }

    public function upload($path,$author_first,$author_last ="_prototype"){
        if(!$this->referer)
            $this->allDocuments();

        $client = new Client([
            'cookies' => $this->session->getCookies(),
        ]);
        $session_id = $this->session->getId();
        $response = $client->request('POST',"https://www.turnitin.com/$this->referer&session-id=$session_id",[
            'cookies' => $this->session->getCookies(),
            'headers' => [
                'Accept' =>'application/json',
                'Origin' => 'https://www.turnitin.com',
                'Referer' => "https://www.turnitin.com/$this->referer",
            ],
            'multipart' => [
                [
                    'name'      => 'async_request',
                    'contents'  => 1,
                ],
                [
                    'name'      => 'userID',
                    'contents'  => null,
                ],
                [
                    'name'      => 'author_first',
                    'contents'  => $author_first,
                ],
                [
                    'name'      => 'author_last',
                    'contents'  => $author_last,
                ],
                [
                    'name'      => 'title',
                    'contents'  => md5(date('d-m-Y H:i:s').''.rand(0,100)),
                ],
                [
                    'name'      => 'userfile',
                    'contents'  => fopen($path,'r'),
                    'filename'  => 'Proposal.docx'
                ],
                [
                    'name'      => 'db_doc',
                    'contents'  => null,
                ],
                [
                    'name'      => 'dropbox_filename',
                    'contents'  => null,
                ],
                [
                    'name'      => 'google_doc',
                    'contents'  => null,
                ],
                [
                    'name'      => 'google_auth_uri',
                    'contents'  => null,
                ],
                [
                    'name'      => 'token',
                    'contents'  => null,
                ],
                [
                    'name'      => 'submit_via_panda',
                    'contents'  => 1,
                ],
                [
                    'name'      => 'submit_button',
                    'contents'  => null,
                ],
            ]
        ]);
        // wis ngisi form langsung upload dan dicek metadatane
        $status = 0;
        while(!$status){
            $response = $client->request('GET',"https://www.turnitin.com/panda/get_submission_metadata.asp?session-id=$session_id&lang=en_us&skip_ready_check=1",[
                'cookies' => $this->session->getCookies(),
                'headers' => [
                    'accept-encoding' => 'gzip, deflate',
                    'session-id' => $this->session->getId()
                ]
            ]);
            $jsonStatus = json_decode($response->getBody());
            $status = $jsonStatus->status;
            sleep(1);
        }
        // acc
        $response = $client->request('POST',"https://www.turnitin.com/submit_confirm.asp?lang=en_us&session-id=$session_id&data-state=confirm",[
            'cookies' => $this->session->getCookies(),
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'Accept' =>'application/json',
                'Origin' => 'https://www.turnitin.com',
                'Referer' => "https://www.turnitin.com/$this->referer",
                'session-id' => $this->session->getId(),
            ]
        ]);
        $jsonConfirm = json_decode($response->getBody());
        return $jsonConfirm;
    }
    private function minify($buffer){
        if(strpos($buffer,'<pre>') !== false)
        {
            $replace = array(
                '/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"                  => '<?php ',
                "/\r/"                      => '',
                "/>\n</"                    => '><',
                "/>\s+\n</"    				=> '><',
                "/>\n\s+</"					=> '><',
            );
        }
        else
        {
            $replace = array(
                '/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"                  => '<?php ',
                "/\n([\S])/"                => '$1',
                "/\r/"                      => '',
                "/\n/"                      => '',
                "/\t/"                      => '',
                "/ +/"                      => ' ',
            );
        }
        $buffer = preg_replace(array_keys($replace), array_values($replace), $buffer);
        return $buffer;
    }
    public function getReport($oid){
        $client = new Client([
            'cookies' => $this->session->getCookies(),
        ]);
        $response = $client->request('GET',"https://ev.turnitin.com/app/carta/en_us/?lang=en_us&o=$oid",[
            'cookies' => $this->session->getCookies(),
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $this->session->getId()
            ]
        ]);
        $response = $client->request('GET',"https://www.turnitin.com/newreport_classic.asp?lang=en_us&oid=$oid&ft=1&bypass_cv=1",[
            'cookies' => $this->session->getCookies(),
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $this->session->getId()
            ]
        ]);
        $data = $response->getBody();
        preg_match_all('/similarity_percent">(\d+)%<\/div>/',$data,$similarity);
        preg_match_all('/<dd>([^<]+)%/',$data,$similarity_detail);
        if(isset($similarity[1][0])) $similarity = $similarity[1][0];
        $detail = [];
        for($i=0;$i<3;$i++){
            if(isset($similarity_detail[1][$i])){
                $detail[] = $similarity_detail[1][$i];
            }else{
                $detail[] = null;
            }
        }
        $similarity_detail = [];
        $similarity_detail['internet-source'] = intval($detail[0])/100;
        $similarity_detail['publications'] = intval($detail[1])/100;
        $similarity_detail['student-papers'] = intval($detail[2])/100;
        return [
            'similarity' => intval($similarity)/100,
            'similarity_detail' => $similarity_detail,
            'detail' => base64_encode((string)$data),
            'detail_minify' => base64_encode($this->minify((string)$data)),
        ];
    }
}