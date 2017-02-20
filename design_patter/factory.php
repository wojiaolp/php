<?php
/*
 * 工厂模式的最大优点在于创建对象上面，就是把创建对象的过程封装起来，这样随时可以产生一个新的对象。
 * 通俗的说，以前创建一个对象要使用new，现在把这个过程封装起来了
 */

class Driver_mysql{
	public function __construct(){
		echo "I am mysql!\n";
	}
}

class Driver_oracle{
	public function __construct(){
		echo "I am oracle!\n";
	}
}

class DbFactory {
	
	static function factory($dbClass){
		$className = "Driver_{$dbClass}";
		if(class_exists($className)){
			return new $className;
		}else{
			echo "class not fonud!\n";
		}
	}
}

DbFactory::factory("mysql");
DbFactory::factory("oracle");
DbFactory::factory("sqlserver");
