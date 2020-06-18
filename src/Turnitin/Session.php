<?php
namespace reyzeal\Turnitin;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use http\Env;

class Session extends SessionAdapter {
    private $username,$password,$cookies,$id,$forceToLogin;
    public $isLogin = false;
    public function __construct($username,$password,$path,$forceToLogin=false){
        $this->username=$username;
        $this->password=$password;
        $this->forceToLogin = $forceToLogin;
        $this->filename = 'session_'. md5($username.$password);
        $this->isLogin = null;
        $check = $this->check();
        if(!$check) $this->login();
    }
    private function check(){
        $client = new Client([
            'cookies' => $this->getCookies(),
        ]);
        $response = $client->head('https://www.turnitin.com/t_home.asp',[
            'allow_redirects'=>false,
            'headers' => [
                'accept-encoding' => 'gzip, deflate',
                'session-id' => $this->getId()
            ]
        ]);
        $html = $response->getStatusCode();
        return $html == 200;
    }

    public function login(){
        if(!$this->forceToLogin){
            $data = $this->retrieve();
            if($data){
                $this->cookies = $data['cookies'];
                $this->id = $data['session_id'];
                $this->isLogin = $this->check();
                if($this->isLogin) return $data;
            }
        }

        $cookiejar = new CookieJar();
        $client = new Client([
            'cookies' => $cookiejar,
            'timeout' => 60,
        ]);
        $response = $client->request('POST','https://www.turnitin.com/login_page.asp',[
            'form_params' => [
                'email'=>$this->username,
                'submit'=>'Log in',
                'user_password'=>$this->password,
            ],
        ]);
        $cookie = $response->getHeader('Set-Cookie');
        preg_match_all('/session-id=([\w\d]+)/',$cookie[0],$session_id);
        $session_id = $session_id[1][0];
        $this->cookies = $cookiejar;
        $this->id = $session_id;
        $data = [
            'cookies' => $cookiejar,
            'session_id' => $session_id
        ];
        $this->save($data);
        $this->isLogin = true;
        return $data;
    }
    public function getCookies(){
        return $this->cookies;
    }
    public function getId(){
        return $this->id;
    }
}