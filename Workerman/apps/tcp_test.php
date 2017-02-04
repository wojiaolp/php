<?php 
use Workerman\Worker;
require_once __DIR__ . '/../Autoloader.php';

$tcp_worker = new Worker("tcp://0.0.0.0:2347");

$tcp_worker->count = 4;

$tcp_worker->onMessage = function($connection, $data){
	$connection->send('hello ' . $data);
};

Worker::runAll();

?>
