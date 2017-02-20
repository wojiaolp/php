<?php
/*
 * $_instance必须声明为静态的私有变量
 * 构造函数和析构函数必须声明为私有,防止外部程序new类从而失去单例模式的意义
 * getInstance()方法必须设置为公有的,必须调用此方法以返回实例的一个引用
 * ::操作符只能访问静态变量和静态函数
 * new对象都会消耗内存
 * 使用场景:最常用的地方是数据库连接。
 * 使用单例模式生成一个对象后，该对象可以被其它众多对象所使用。
 */

class Singleton {
	//在内存中分配一块固定的空间，其内存地址不变
	private static $_instance = null;
	
	//私有构造函数，防止外界实例化对象
	private function __construct(){
	}

	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			echo "instance\n";
			self::$_instance = new self();
		}else{
			echo "no instance\n";
		}
		return self::$_instance;
	}
	public function test(){
		echo "hello\n";
	}
}

//用new会报错
//$singleton = new Singleton();
$singleton = Singleton::getInstance();
$singleton = Singleton::getInstance();
$singleton->test();
