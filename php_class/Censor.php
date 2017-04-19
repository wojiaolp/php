<?php
class Censor {
	public $config;
	function __construct(){
		$curr_path = dirname ( __FILE__ );
		$censor_file = $curr_path . '/cache_censor.php';
		$this->config = require_once ($censor_file);
	}

	/**
	 * 敏感词过滤
	 * @param unknown $message
	 * @return string|mixed
	 */
	public function filter($message){
		if (empty ( $message )) return '';
		$searches = array();
		$pattern = '/<font color="red">(.*?)<\/font>/';
		preg_match_all ( $pattern, $message, $matches );
		if (!empty($matches[1])){
			$searches = $matches[1];
		}
		$bad_word = array ();
		$replace_word = array ();
		foreach ( $this->config ['mod'] as $mod ) {
			$mod = str_replace ( '||', '|', $mod );
			preg_match_all ( $mod, $message, $matches );
			if (empty ( $matches [1] ))
				continue;
				foreach ( $matches [1] as $mat ) {
					$bad_word [] = '/' . $mat . '/i';
					$replace_word [] = "<font color=\"red\">$mat</font>";
				}
		}
		$bad_word = array_unique ( $bad_word );
		$replace_word = array_unique ( $replace_word );
		if (!empty($bad_word) && !empty($replace_word)) {
			$message = preg_replace ( $bad_word, $replace_word, $message );
		}
		return $message;
	}

	/**
	 * 检查是否含有敏感词
	 * @param unknown $message
	 * @return boolean
	 */
	public function check($message){
		if (empty ( $message )) return '';
		foreach ( $this->config ['mod'] as $mod ){
			$mod = str_replace ( '||', '|', $mod );
			preg_match_all ($mod, $message, $matches);
			if (!empty($matches[1])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 对敏感词过滤替换成***
	 * @param unknown $message
	 * @return string|unknown|mixed
	 */
	public function replace($message){
		if (empty ( $message )) return '';
		$searches = array ();
		$replaces = array ();
		$pattern = '/<font color="red">(.*?)<\/font>/';
		preg_match_all($pattern, $message, $matches);
		if (empty($matches[1])) {
			return $message;
		}else{
			foreach ($matches [1] as $mat ) {
				$searches[] = '/<font color="red">' . $mat . '<\/font>/';
				$replaces[] = $this->getTag($mat);
			}
		}

		if (!empty($searches) && !empty($replaces)){
			$message = preg_replace ($searches, $replaces, $message );
		}
		return $message;
	}

	/**
	 * 对标红敏感词进行还原
	 * @param unknown $message
	 * @return string|unknown|mixed
	 */
	public function recover($message){
		if (empty ( $message )) return '';
		$searches = array ();
		$replaces = array ();
		$pattern = '/<font color="red">(.*?)<\/font>/';
		preg_match_all ( $pattern, $message, $matches );
		if (empty ( $matches [1] )) {
			return $message;
		} else {
			foreach ( $matches [1] as $mat ) {
				$searches [] = '/<font color="red">' . $mat . '<\/font>/';
				$replaces [] = $mat;
			}
		}

		if (! empty ( $searches ) && ! empty ( $replaces )) {
			$message = preg_replace ( $searches, $replaces, $message );
		}
		return $message;
	}

	/**
	 * 生成替换***
	 * @param unknown $keys
	 * @return string
	 */
	private function getTag($keys){
		$tag = '';
		$num = strlen($keys)/2;
		for ($i = 1; $i <= $num; $i++){
			$tag .= '*';
		}
		return $tag;
	}
}