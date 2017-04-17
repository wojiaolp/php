<?php
class Hook{
	private static $actions = array();
	
	public static function add_action($hook,$function){
		$hook = strtolower($hook);
		if(!self::exists_action($hook)){
			self::$actions[$hook] = array();
		}
		if(is_callable($function)){
			self::$actions[$hook][] = $function;
			return TRUE;
		}
	}
	
	public static function exists_action($hook){
		$hook = strtolower($hook);
		return (isset(self::$actions[$hook]))?TRUE:FALSE;
	}
	
	public static function do_action($hook,$params=NULL){
		$hook = strtolower($hook);
		if(isset(self::$actions[$hook])){
			foreach(self::$actions[$hook] as $function){
				if(is_array($params)){
					call_user_func_array($function,$params);
				}else{
					call_user_func($function);
				}
			}
		}
	}
	
	public static function get_action($hook)
    {
        $hook=strtolower($hook);
        return (isset(self::$actions[$hook]))? self::$actions[$hook]:FALSE;
    }
	
	public static function action_list(){
		print_r(self::$actions);
	}
}

class some_class{
	public static function hook_test(){
		echo "some_class::hook_test\n";
	}
}


Hook::add_action('unique_name_hook','some_class::hook_test');
Hook::action_list();
Hook::do_action('unique_name_hook');