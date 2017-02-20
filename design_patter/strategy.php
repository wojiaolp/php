<?php
/*
 * 策略模式:定义了算法族,分别封装起来，让它们之间可以互相替换，此模式让算法的变化独立于使用算法的客户。
 * 封装:把行为用接口封装起来，我们可以把那些经常变化的部分，从当前的类中单独取出来，用接口进行单独的封装。
 * 互相替换:我们封装好了接口，通过指定不同的接口实现类进行算法的变化。
 */

interface FlyBehavior{
	public function fly();
}

class FlyWithWings implements FlyBehavior{
	public function fly(){
		echo "fly with wings\n";
	}
}

class FlyWithNo implements FlyBehavior{
	public function fly(){
		echo "fly with no wings\n";
	}
}

class Duck{
	private $_flyBehavior;

	public function setFlyBehavior(FlyBehavior $behavior){
		$this->_flyBehavior = $behavior;
	}
	
	public function duckFly(){
		$this->_flyBehavior->fly();
	}

	public function duckSay(){
		echo "gua gua gua!\n";
	}

}

class RubberDuck extends Duck{
}

$rbDuck = new RubberDuck();
$rbDuck->duckSay();
$rbDuck->setFlyBehavior(new FlyWithNo());
$rbDuck->duckFly();


