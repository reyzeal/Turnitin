<?php
namespace reyzeal\Turnitin;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use http\Env;

class Session extends SessionAdapter {
    private $username,$password,$cookies,$id,$forceToLogin;
    public $isLogin = false;
    public function __construct($username,$password,$path,$forceToLogin=false){
        parent::__construct($path);
        $this->username=$username;
        $this->password=$password;
        $this->forceToLogin = $forceToLogin;
        $this->filename = 'session_'. md5($username.$password);
    }

    public function login(){
        if(!$this->forceToLogin){
            $data = $this->retrieve();
            $this->cookies = $data['cookies'];
            $this->id = $data['session_id'];
            $this->isLogin = true;
            return $data;
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
        if(!$this->isLogin){
            $this->login();
        }
        return $this->cookies;
    }
    public function getId(){
        if(!$this->isLogin){
            $this->login();
        }
        return $this->id;
    }
}