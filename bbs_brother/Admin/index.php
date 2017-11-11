<?php
	

	//开启session
	session_start();
	
	//加载配置文件
	require_once "./Conf/config.php";

	//引入控制器文件
	function __autoload($name)
	{
			
		try{
			if(file_exists('./Controller/'.$name.'.class.php')){
				require "./Controller/".$name.".class.php";
			}else if(file_exists('./Libs/'.$name.'.class.php')){
				require "./Libs/".$name.".class.php";
			}else if(file_exists('./Model/'.$name.'.class.php')){
				require_once "./Model/Model.class.php";
			}else{
				throw new Exception('该类文件不存在！请检查！','250');
			}
		}catch(Exception $e){
			echo $e->xdebug_message;
			die;
		}
	}

	//接收用户提交的控制器和方法名
	$contro = isset($_GET['c']) ? $_GET['c'] : 'Index';
	$action = isset($_GET['a']) ? $_GET['a'] : 'index';

	//格式化控制器名
	$controller = ucfirst($contro.'Controller');

	//格式化方法名
	$action = strtolower($action);



	//入口文件
	$user = new $controller();

	//判断是否有该方法
	if(!method_exists($user,$action)){
		echo "<script>window.location.href='./View/Public/404.html'</script>";
	}
	//加载方法
	$user->$action();