<?php

/*其实最简单的例子是当我们引用一个第三方类库。这个类库随着版本的改变，它提供的API也可能会改变。如果很不幸的是，你的应用里引用的某个API已经发生改变的时候，你就得去硬着头皮去改大量的代码。 
 */

abstract class Toy{
	public abstract function openMouth();
	
	public abstract function closeMouth();
	
	//公司A
	//public abstract function doMouthOpen();
	//public abstract function doMouthClose();
	
	//公司B
	//public abstract function operateMouth($type = 0)  //type为0则“闭嘴”，反之张嘴	
}

class Dog extends Toy{
	public function openMouth(){
		echo "Dog open mouth\n";
	}

	public function closeMouth(){
		echo "Dog close mouth\n";
	}
	/*
	public function doMouthOpen(){
		//$this->openMouth();
	}
	*/
	/*
	public function operateMouth($type=0){
		if($type == 0){
			$this->closeMouth();
		}else{
			$this->openMouth();
		}
	}
	*/
}

//$dog = new Dog();
//$dog->openMouth();
//$dog->doMouthOpen();

//像上面那样编写代码，代码实现违反了“开-闭”原则，一个软件实体应当对扩展开放，对修改关闭。即在设计一个模块的时候，应当使这个模块可以在不被修改的前提下被扩展。

//公司A
interface ComATarget  {  
	public function doMouthOpen();  
	      
	public function doMouthClose();  
}  

//公司B
interface ComBTarget  
{  
	public function operateMouth($type = 0);  
}

//公司A配适器
class ComAAdapter implements ComATarget{
	private $adaptee;
	function __construct(Toy $adaptee){
		$this->adaptee = $adaptee;
	}
	
	public function doMouthOpen(){
		$this->adaptee->openMouth();
	}

	public function doMouthClose(){
		$this->adaptee->closeMouth();
	}

}

//公司B配适器
class ComBAdapter implements ComBTarget{
	private $adaptee;
	function __construct(Toy $adaptee){
		$this->adaptee = $adaptee;
	}
	public function operateMouth($type = 0){
		if($type){
			$this->adaptee->openMouth();
		}else{
			$this->adaptee->closeMouth();
		}
	}
}
$adaptee_dog = new Dog();
$adapter_comA = new ComAAdapter($adaptee_dog);
$adapter_comA->doMouthOpen();

$adapter_comB = new ComBAdapter($adaptee_dog);
$adapter_comB->operateMouth(0);

