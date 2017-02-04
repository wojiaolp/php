<?php
use Workerman\Worker;
require_once __DIR__."/../Autoloader.php";

$http_worker = new Worker("JsonNL://0.0.0.0:2348");
$http_worker->count = 4;

$http_worker->onMessage = function($connection, $data){
	$connection->send('data:'.json_encode($data));
};

Worker::runAll();
?>