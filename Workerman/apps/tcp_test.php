<?php 
use Workerman\Worker;
use Workerman\Lib\Timer;

require_once __DIR__ . '/../Autoloader.php';

$tcp_worker = new Worker("tcp://0.0.0.0:2347");
//Worker::$daemonize = true;
$tcp_worker->count = 4;

$tcp_worker->onWorkerStart = function($tcp_worker){
	Timer::add(2,function()use($tcp_worker){
		foreach($tcp_worker->connections as $connection){
			$connection->send(time());
		}	
	});
};

$tcp_worker->onMessage = function($connection, $data){
	$connection->send('hello ' . $data);
};

Worker::runAll();

?>
