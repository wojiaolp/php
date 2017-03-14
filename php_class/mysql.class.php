<?php
class mysql_db
{
	var $db_link = NULL;
	var $db_res = NULL;
	var $db_name = NULL;
	var $database = NULL;
	var $url = '';
	var $debug = true;
	var $_sql = '';//检查mysql错误
	static $connect_num = 1;

	function xSelectServer( $xHostType, $xMixedMode = true ){
		global $_XCAR_DB_HOST_GROUP;
		if ( $xMixedMode == true )
			$lIndex = mt_rand( 0, count( $_XCAR_DB_HOST_GROUP[$xHostType] )-1 );
		else
			$lIndex = mt_rand( 1, count( $_XCAR_DB_HOST_GROUP[$xHostType] )-1 );
		return $_XCAR_DB_HOST_GROUP[$xHostType][$lIndex];
	}

	function connect($new_link = false){

		$this->url=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		global $xcar_config_db;
		$this->db_link = @mysql_connect($xcar_config_db[$this->db_name]['host'],$xcar_config_db[$this->db_name]['user'],$xcar_config_db[$this->db_name]['pass'],$new_link);
		if($this->db_link == false){
			$this->ping();
		}
		$this->select_db($this->database,$this->db_link);
	}

	function ping(){
		self::$connect_num++;
		//	if($this->debug){

		//	}
		if(!@mysql_ping($this->db_link)){
			if(self::$connect_num>3){
				$this->halt('Can not Connect to Mysql Server!');
			}else{
				@mysql_close($this->db_link);
				$this->connect();

			}

		}

	}
	function select_db($db_name = NULL , $db_link = NULL)
	{
		if($db_name == NULL)
		{
			$this->halt('Function select_db() Missing db_name!');
		}
		if($db_link == NULL)
		{
			if($this->db_link == NULL)
			{
				$this->halt('Function select_db() Missing Link Identifier!');
			}
			$db_link = $this->db_link;
		}
		if(mysql_select_db($db_name,$db_link) == false)
		{
			$this->halt("Can not Select Database '$db_name'!");
		}
	}

	function query($sql_query = NULL , $db_link = NULL)
	{

		 if(stripos($sql_query,'elect')){
			if(strpos($sql_query,';')){
				$sql_query=str_replace(';','',$sql_query);
			}
           $sql_query = $sql_query."  /*  ".$_SERVER['SERVER_ADDR'].$_SERVER['SCRIPT_FILENAME']." */";
		 }

		//查找慢查询start
		$this->_sql = $sql_query;

		$findarr = array('news_info_shadow',';');
		$notappearance= array('news_info_key');

		//$findarr = array(' news_info ');

		$flag  = true;
		$have = array();
		foreach($findarr as $find){
			if(stripos($sql_query,$find)){
				$have[$find]=1;
			}else{
				$have[$find]=0;
			}
		}

		foreach($have as $h){
			if(!$h){
				$flag=false;
			}
		}
		foreach($notappearance as $appearance){
			if(stripos($sql_query,$appearance)){
				$flag=false;
			}
		}


		if($flag){
			$filename=$_SERVER['SCRIPT_FILENAME']."__".$_SERVER['REQUEST_URI'];
			$url = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			error_log($filename."\t".$sql_query."\t".$url."\r\n",3,"/tmp/slowsql".date("Ymd").".sql");
		}

		//查找慢查询end

		//专题停止评论  1可以评论  0不能评论
		$comment=1;
		if(!$comment){
			if(stripos($sql_query,"xcar_small_message") && stripos($sql_query,"NSERT INTO")){
				return true;

			}
		}
		//结束





		if($sql_query == NULL)
		{
			$this->halt('Function Query() Missing SQL Query!');
		}
		if($db_link == NULL)
		{
			if($this->db_link == NULL)
			{
				$this->halt('Function Query() Missing Link Identifier!');
			}
			$db_link = $this->db_link;
		}
		if(mysql_select_db($this->database,$db_link) == false)
		{
			$this->halt("Can not Select Database '$this->database'!");
		}

		$this->db_res = @mysql_query($sql_query , $db_link);

		if($this->db_res == false)
		{
			$this->halt('Invalid SQL Words or Query Error!' , $sql_query);
		}





		return 	 $this->db_res;
	}

	function next_record($db_res = NULL)
	{
		if($db_res == NULL)
		{
			if($this->db_res == NULL)
			{
				$this->halt('Function  next_record() Missing Resouce link_identifier!');
			}
			$db_res = $this->db_res;
		}
		return mysql_fetch_array($db_res);
	}
	  /*
	新加的得到结果集
	$row 是不是只取一行   m多行  1只有1行
	$type 0只有数字key,1只有字母key,2两者都有
		2011-11-17
		张军涛

	   */
	function get_rows($sql,$rownum='m',$type=1){
		$typearr=array(0=>MYSQL_NUM,1=>MYSQL_ASSOC,2=>MYSQL_BOTH);
		$res = $this->query($sql);
		$ret = array();
		while($row=mysql_fetch_array($res,$typearr[$type])){
			$ret[]=$row;
		}
		if(count($ret)==1 && $rownum=='1'){
			$ret = $ret[0];
		}
		return $ret;
	}

	/*
	 新加的得到结果集
	$row 是不是只取一行
	$type 0只有数字key,1只有字母key,2两者都有
	2012-10-17
	张军涛

	 */
	 function get_one($sql,$rownum='1',$type=1){
	 return $this->get_rows($sql,$rownum='1',$type);
	 }
	function num_rows($db_res = NULL)
	{
		if($db_res == NULL)
		{
			if($this->db_res == NULL)
			{
				$this->halt('Function  num_rows() Missing Resouce link_identifier!');
			}
			$db_res = $this->db_res;
		}
		return mysql_num_rows($db_res);
	}

	function affected_rows()
	{
		if($this->db_link == NULL)
		{
			$this->halt('Function  affected_rows() Missing Resouce link_identifier!');
		}
		$db_link = $this->db_link;
		return mysql_affected_rows($db_link);
	}

	function halt($error_msg,$sql=NULL)
	{
		$msg = mysql_errno().mysql_error();
		$msg.=$_SERVER['SCRIPT_FILENAME']."__".$_SERVER['REQUEST_URI']."__".$this->_sql."\n";
		error_log($msg,3,"/tmp/mysqlerr".date("Ymd").".log");
		//chmod("/tmp/mysqlerr".date("Ymd").".log", 0777);
		#print mysql_errno();
		#print "<br>";
		#print mysql_error();
		#print "<br>";
		#exit("数据库错误！");
		exit;
	}

	function close()
	{
		if($this->db_link == NULL){
			$this->halt('Function close() Missing Link Identifier!');
		}
		$result = @mysql_close($this->db_link);
		if($result == FALSE) {
			$this->halt('Can not close Current db_link!');
		} else {
			$this->db_link = NULL;
		}
	}

	function insert_id()
	{
		if($this->db_link == NULL){
			$this->halt('Function close() Missing Link Identifier!');
		}
		return mysql_insert_id ($this->db_link);
	}

	///////////////////////////////////////////////////////////////////
	// 论坛的数据库类 db_mysql.php 里的部分函数，以后可能会用不上
	///////////////////////////////////////////////////////////////////

	function query_s($sql, $type = '', $db_link = NULL) {

		if($db_link == NULL)
		{
			if($this->db_link == NULL)
			{
				$this->halt('Function Query() Missing Link Identifier!');
			}
			$db_link = $this->db_link;
		}
		if(mysql_select_db($this->database,$db_link) == false)
		{
			$this->halt("Can not Select Database '$this->database'!");
		}

		if($type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query')) {
			$query = mysql_unbuffered_query($sql,$db_link);
		} else {
			if($type == 'CACHE' && intval(mysql_get_server_info()) >= 4) {
				$sql = 'SELECT SQL_CACHE'.substr($sql, 6);
			}
			if(!($query = mysql_query($sql,$db_link)) && $type != 'SILENT') {
				$this->halt('MySQL Query Error', $sql);
			}
		}
		$this->querynum++;

		return $query;
	}

	function fetch_array($db_res = NULL)
	{
		if($db_res == NULL)
		{
			if($this->db_res == NULL)
			{
				$this->halt('Function  next_record() Missing Resouce link_identifier!');
			}
			$db_res = $this->db_res;
		}
		return mysql_fetch_array($db_res);
	}

	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	///////////////////////////////////////////////////////////////////
}


///////////////////////////////////////////////////
class BBS_SQL extends mysql_db
{
	var $new_link_ly;
	function BBS_SQL( $new_link = false )
	{
		$this->new_link_ly=$new_link;
		$this->db_name = 'bbsdb_s';
		$this->database = 'xcardb';
		$this->connect($this->new_link_ly);

	}

	function query($sql_query = NULL , $db_link = NULL){
		if($sql_query == NULL){
			$this->halt('Function Query() Missing SQL Query!');
		}
		if(eregi("bbs_sessions",$sql_query) || eregi("bbs_sp_",$sql_query)){

			if($this->db_name!='bbsdb_session'){
				$this->db_name = 'bbsdb_session';
				$this->database = 'xcardb';
				$this->connect($this->new_link_ly);
			}
		}else{
			if(strtolower(substr(trim($sql_query),0,6))=='select'){
				if($this->db_name!='bbsdb_s'){
					$this->db_name = 'bbsdb_s';
					$this->database = 'xcardb';
					$this->connect($this->new_link_ly);
				}
			}else{
				if($this->db_name!='bbsdb'){
					$this->db_name = 'bbsdb';
					$this->database = 'xcardb';
					$this->connect($this->new_link_ly);
				}
			}
		}

		if($db_link == NULL){
			if($this->db_link == NULL){
				$this->halt('Function Query() Missing Link Identifier!');
			}
			$db_link = $this->db_link;
		}

		if(@mysql_select_db($this->database,$db_link) == false){
			$this->halt("Can not Select Database '$this->database'!");
		}

		$this->db_res = @mysql_query($sql_query , $db_link);

		if($this->db_res == false){
			$this->halt('Invalid SQL Words or Query Error!' , $sql_query);
		}

		return   $this->db_res;
	}

}


class BBS_SQL_S extends mysql_db
{
	var $new_link_ly;
	function BBS_SQL( $new_link = false )
	{
		$this->new_link_ly=$new_link;
		$this->db_name = 'bbsdb_s';
		$this->database = 'xcardb';
		$this->connect($this->new_link_ly);
	}

	function query($sql_query = NULL , $db_link = NULL){
		if($sql_query == NULL){
			$this->halt('Function Query() Missing SQL Query!');
		}
		if(eregi("bbs_sessions",$sql_query) || eregi("bbs_sp_",$sql_query)){

			if($this->db_name!='bbsdb_session'){
				$this->db_name = 'bbsdb_session';
				$this->database = 'xcardb';
				$this->connect($this->new_link_ly);
			}
		}else{
			if(strtolower(substr(trim($sql_query),0,6))=='select'){
				if($this->db_name!='bbsdb_s'){
					$this->db_name = 'bbsdb_s';
					$this->database = 'xcardb';
					$this->connect($this->new_link_ly);
				}
			}else{
				if($this->db_name!='bbsdb'){
					$this->db_name = 'bbsdb';
					$this->database = 'xcardb';
					$this->connect($this->new_link_ly);
				}
			}
		}

		if($db_link == NULL){
			if($this->db_link == NULL){
				$this->halt('Function Query() Missing Link Identifier!');
			}
			$db_link = $this->db_link;
		}

		if(@mysql_select_db($this->database,$db_link) == false){
			$this->halt("Can not Select Database '$this->database'!");
		}

		$this->db_res = @mysql_query($sql_query , $db_link);

		if($this->db_res == false){
			$this->halt('Invalid SQL Words or Query Error!' , $sql_query);
		}

		return   $this->db_res;
	}

}

///////////////////////////////////////////////////

///////////////////////////////////////////////////

class NEWS_SQL extends mysql_db
{
	function NEWS_SQL( $new_link = false )
	{
		$this->db_name = 'newsdb';
		$this->database = 'xcardb2';
		$this->connect($new_link);
	}
}

class XNEWS_SQL_S extends mysql_db
{
    function XNEWS_SQL_S( $new_link = false )
    {
        $this->db_name = 'newsdb_s';
        $this->database = 'xcardb2';
        $this->connect($new_link);
    }
}

class NEWS_SQL_S extends mysql_db
{
	function NEWS_SQL_S(  $new_link = false , $xMixedMode = true )
	{
		$this->db_name = $this->xSelectServer( 'newsdb', $xMixedMode );
		$this->database = 'xcardb2';
		$this->connect($new_link);
	}
}

///////////////////////////////////////////////////


///////////////////////////////////////////////////

class NEWS_Xbackend_SQL extends mysql_db
{
	function NEWS_Xbackend_SQL( $new_link = false )
	{
		$this->db_name = 'news_backend';
		$this->database = 'xbackend';
		$this->connect($new_link);
	}
}


class NEWS_Xbackend_SQL_S extends mysql_db
{
	function NEWS_Xbackend_SQL_S(  $new_link = false , $xMixedMode = true )
	{
		$this->db_name = $this->xSelectServer( 'newsdb', $xMixedMode );
		$this->database = 'xbackend';
		$this->connect($new_link);
	}
}

///////////////////////////////////////////////////


///////////////////////////////////////////////////

class Sendmail_SQL extends mysql_db
{
	function Sendmail_SQL( $new_link = false )
	{
		$this->db_name = 'bbsdb';
		$this->database = 'sendmail';
		$this->connect($new_link);
	}
}

class Sendmail_SQL_S extends mysql_db
{
	function Sendmail_SQL_S(  $new_link = false , $xMixedMode = true )
	{
		$this->db_name = $this->xSelectServer( 'bbsdb', $xMixedMode );
		$this->database = 'sendmail';
		$this->connect($new_link);
	}
}

///////////////////////////////////////////////////


///////////////////////////////////////////////////

class SMS_SQL extends mysql_db
{
	function SMS_SQL( $new_link = false )
	{
		$this->db_name = 'smsdb';
		$this->database = 'sms';
		$this->connect($new_link);
	}
}


class SMS_SQL_S extends mysql_db
{
	function SMS_SQL_S(  $new_link = false , $xMixedMode = true )
	{
		$this->db_name = $this->xSelectServer( 'smsdb', $xMixedMode );
		$this->database = 'sms';
		$this->connect($new_link);
	}
}

///////////////////////////////////////////////////


///////////////////////////////////////////////////

class Picbed  extends mysql_db
{
	function Picbed( $new_link = false ){
		$this->db_name  = 'picbed';
		$this->database = 'CrazyDance';
		$this->connect($new_link);
	}
}

//图库的从库2015-1-19张军涛
class Picbed_S  extends mysql_db
{
	function Picbed_S( $new_link = false ){
		$this->db_name  = 'picbed_s';
		$this->database = 'CrazyDance';
		$this->connect($new_link);
	}
}


class CrazyDance  extends mysql_db
{
	function CrazyDance( $new_link = false ){
		$this->db_name  = 'DAHLIA';
		$this->database = 'DAHLIA';
		$this->connect($new_link);
	}
}


class CrazyDance_M  extends mysql_db
{
	function CrazyDance_M( $new_link = false ){
		$this->db_name  = 'DAHLIA_M';
		$this->database = 'DAHLIA';
		$this->connect($new_link);
	}
}

class CrazyDance_S  extends mysql_db
{
	function CrazyDance_S( $new_link = false ){
		$this->db_name  = 'DAHLIA_S';
		$this->database = 'DAHLIA';
		$this->connect($new_link);

	}
}

/*修改为真名的数据库 */
if(!class_exists('DAHLIA',FALSE)){
	class DAHLIA  extends mysql_db
	{
		function DAHLIA( $new_link = false ){
			$this->db_name  = 'DAHLIA_S';
			$this->database = 'DAHLIA';
			$this->connect($new_link);

		}
	}
}



class CrazyDance_FLUX  extends mysql_db
{
	function CrazyDance_FLUX( $new_link = false ){
		$this->db_name  = 'flux_manage';
		$this->database = 'flux_manage';
		$this->connect($new_link);
	}
}



class GoCar  extends mysql_db
{
	function  GoCar($new_link = false )
	{
		$this->db_name = 'smsdb2';
		$this->database = 'gocar';
		$this->connect($new_link);
	}
}



class AGoCar  extends mysql_db
{
	function  AGoCar($new_link = false )
	{
		$this->db_name = 'agocar';
		$this->database = 'gocar';
		$this->connect($new_link);
	}
}

class GoCarRead  extends mysql_db
{
	function  GoCarRead($new_link = false )
	{
		$this->db_name = 'smsdb2_s';
		$this->database = 'gocar';
		$this->connect($new_link);
	}
}
///////////////////////////////////////////////////


class VOTE  extends mysql_db
{
	function VOTE( $new_link = false )
	{
		$this->db_name = 'vote';
		$this->database = 'vote';
		$this->connect($new_link);
	}
}

class PMS  extends mysql_db
{
	function PMS( $new_link = false )
	{
		$this->db_name = 'bbsdb_pms';
		$this->database = 'xcardb';
		$this->connect($new_link);
	}
}


class XTV_DB  extends mysql_db
{
	function XTV_DB( $new_link = false )
	{
		$this->db_name = 'xtv_db';
		$this->database = 'xtv';
		$this->connect($new_link);
	}
}

class XTV_DB_S  extends mysql_db
{
	function XTV_DB_S( $new_link = false )
	{
		$this->db_name = 'xtv_db_s';
		$this->database = 'xtv';
		$this->connect($new_link);
	}
}

class NEWSPV_DB  extends mysql_db
{
	function NEWSPV_DB( $new_link = false )
	{
		$this->db_name = 'NEWSPV_DB';
		$this->database = 'pv';
		$this->connect($new_link);
	}
}

class KOUBEI extends mysql_db
{
	function KOUBEI( $new_link = false )
	{
		$this->db_name = 'KOUBEI';
		$this->database = 'koubei';
		$this->connect($new_link);
	}
}

class KOUBEI2BBS extends mysql_db
{
	function KOUBEI2BBS( $new_link = false )
	{
		$this->db_name = 'KOUBEI2BBS';
		$this->database = 'xcardb';
		$this->connect($new_link);
	}
}

class CHEYP extends mysql_db
{
	function CHEYP( $new_link = false )
	{
		$this->db_name = 'YP_DB';
		$this->database = 'cheyp';
		$this->connect($new_link);
	}
}

class XCAR_APP  extends mysql_db
{
	function XCAR_APP( $new_link = false ){
		$this->db_name  = 'XCAR_APP';
		$this->database = 'XCAR_APP';
		$this->connect($new_link);

	}
}

class TAG_DB extends mysql_db{
	function TAG_DB( $new_link = false ){
		$this->db_name  = 'tag';
		$this->database = 'tag';
		$this->connect($new_link);
	}
}

class TAG_DB_S extends mysql_db{
	function TAG_DB_S( $new_link = false ){
		$this->db_name  = 'tag_s';
		$this->database = 'tag';
		$this->connect($new_link);
	}
}

class WIKI_DB extends mysql_db{
	function WIKI_DB( $new_link = false ){
		$this->db_name  = 'tag';
		$this->database = 'xcar_wiki';
		$this->connect($new_link);
	}
}

class WIKI_DB_S extends mysql_db{
	function WIKI_DB_S( $new_link = false ){
		$this->db_name  = 'tag_s';
		$this->database = 'xcar_wiki';
		$this->connect($new_link);
	}
}

?>