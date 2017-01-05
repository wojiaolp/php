<?php
//socket服务器
/*
+---------------------------
* @创建socket server整个过程
+---------------------------
* @socket_create
* @socket_bind
* @socket_listen
* @socket_accept
* @socket_read
* @socket_write
* @socket_close
+---------------------------
*/

error_reporting(E_ALL);
set_time_limit(0);

//检测php是否支持socket
if(!extension_loaded('sockets')){
	die('The sockets extension is not loaded.');
}

//server的地址和端口
$address = '127.0.0.1';
$port = '10000';

//创建socket链接
$mysock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");

//绑定地址和端口
socket_bind($mysock, $address, $port) or die("Could not bind to socket \n");

//监听
socket_listen($mysock,5) or die("Could not set up socket listener\n");

echo "Server started,accepting connections...\n";

//Socket来处理通信。这里会阻塞等待
$client	= socket_accept($mysock) or die("Could not accept incoming connection\n");

//发到客户端
$msg = "congratulation! you success!\n";
socket_write($client, $msg, strlen($msg));
echo "send to client: {$msg}\n";

//接收客户端消息
$buf = socket_read($client,8192);
echo "recvie from client: {$buf}\n";

echo "Closing sockets...\n";
socket_close($client);
socket_close($mysock);
	
?>
