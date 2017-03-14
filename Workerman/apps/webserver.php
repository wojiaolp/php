<?php

use Workerman\Worker;
use Workerman\WebServer;

require_once __DIR__."/../Autoloader.php";

$web = new WebServer('http://0.0.0.0:21231');
$web->addRoot('localhost', __DIR__ . '/web');

Worker::runAll();
