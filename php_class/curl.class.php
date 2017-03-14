<?php
/**
 * 封装CURL，接口POST、GET方法,默认带Cookie，可以取消
 *----------------------------------------------------------------------- +
 * @ author wang.cun
 * @ date  2013/08/29
 * @ 支持POST GET file_get_contents
 * @ 支持多元Cookie传递
 *   - boole 会自动获取本地中所有Cookie传递 true
 *   - string 多个Cookie字符  a=1;b=2;c=3;
 *   - array  数组 array("a"=>2,"b"=>2)
 *   - 可以进行错误调试 第3个参数开启即可进行调试
 * --------------------------------------------------------
 * 【demo1】 POST 数据
 * $c = icurl::getInstance();
 * $c->post($url,$data);
 *
 * 【demo2】 file_get_contents
 * $c->get($url);
 *
 * 【demo3】 get方式访问数据且附加参数
 * $c->get($url,$data);
 *----------------------------------------------------------------------- +
 **/
class curlHttp{
    public $timeout; //默认超时时间
    public $header;
    public $useragent;
    public $ch;
    public $cookie;
    public $url;
    public $options;

    public function __construct(){
        $this->options = array();
        $this->ch = curl_init();
        $this->setHeader(0);
        $this->setAgent();
        $this->setTimeout();
        $this->setCookie(true);
        $this->options[CURLOPT_RETURNTRANSFER] = true;
    }

    public function setTimeout($t=""){
        $this->timeout = (intval($t) > 0 ) ? $t : 5;
        $this->options[CURLOPT_TIMEOUT] = $this->timeout;
    }

    public function setHeader($flag){
        $this->options[CURLOPT_HEADER] = ($flag) ? 1 : 0;
    }

    public function setRefer($r){
        $this->options[CURLOPT_REFERER] = ($r) ? $r : "spider";
    }

    public function setAgent(){
        $this->useragent="Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36";
        $this->options[CURLOPT_USERAGENT] = $this->useragent;
    }

    public function get($url,$data=array(),$debug= false){
       $this->setUrl($url,$data);
       curl_setopt_array($this->ch,$this->options);
       $r = curl_exec($this->ch);
       if($debug){
           $this->debug();
       }
       $this->closecurl();
       return $r;
    }

    public function post($url,$data=array(),$debug=false){
       $this->setUrl($url);
       $this->options[CURLOPT_POST] = 1;
       $this->options[CURLOPT_POSTFIELDS] = $data;
       curl_setopt_array($this->ch,$this->options);
       $r = curl_exec($this->ch);
       if($debug){
           $this->debug();
       }
       $this->closecurl();
       return $r;
    }

    public function setUrl($url,$data=array()){
        $this->options[CURLOPT_URL] = (empty($data)) ? $url : $url."?".http_build_query($data);
    }

    public function setCookie($data){
        $this->cookie= "";
        if(is_bool($data) && $data){
            $data = $_COOKIE;
            foreach($data as $k=>$v){
                $this->cookie .=$k."=".$v.";";
            }
        }elseif(is_string($data)){
            $this->cookie = $data;
        }elseif(is_array($data)){
            foreach($data as $k=>$v){
                $this->cookie .=$k."={$v};";
            }
        }else{}
        $this->options[CURLOPT_COOKIE] = $this->cookie;
    }

    public function debug(){
        echo "<pre>";
        $errno=curl_errno($this->ch);
        if($errno){
            trigger_error("Curl errno: ".$errno);
        }
        $errmsg=curl_error($this->ch);
        if($errmsg){
            trigger_error("curl errmsg : ".$errmsg);
        }
        echo "\n<br>curl_info:\n<br>";
        print_r(curl_getinfo($this->ch));
    }

    public function closecurl(){
        curl_close($this->ch);
    }

}

class icurlFactory{
	public static function create(){
		return  new curlHttp();
	}
}

class icurl{
    static private $icurl = null;
    private function __construct(){
    }

    static public function getInstance(){
        if(self::$icurl == null){
            self::$icurl = new curlHttp();
        }
        return self::$icurl;
    }
}
?>