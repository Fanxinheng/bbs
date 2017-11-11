<?php

	class LoginController
	{
		//加载登录页
		public function index()
		{
			//判断用户是否登录
			if(isset($_SESSION['uid'])){
				echo "<script>alert('您已登陆！无需重复登录！');window.location.href='./index.php?c=index&a=index'</script>";
				die;
			}

			//实例化post表
			$post = new Model('post');

	//=================封装搜索程序================

			//定义存储搜索信息的数组
			$whereList = array();
			$urlList = array();

			//获取查询的帖子信息
			if(!empty($_REQUEST['title'])){
				$whereList[] = " title like '%{$_REQUEST['title']}%'";
				$urlList[] = "title={$_REQUEST['title']}";
			}

			//定义存储搜素语句的变量
			$where = "";
			$url = "";

			//判断搜索条件是否存在
			if(!empty($whereList)){

				//拼装查询条件
				$where = " && ".implode(' && ',$whereList);

				//拼装地址栏条件
				$url = "&".implode('&',$urlList);
			}
	//=============================================
	
	//================封装分页=====================
	
			//设置分页参数
			$page = isset($_GET['p'])?$_GET['p']:'1';	//当前页码
			$maxRows = 0;	//总条数
			$pageSize = 5;	//每页条数
			$maxPage = 0;	//总页数

			//获取总条数
			$maxRows = $post->query("select count(*) as sum from post where recycle=0 ".$where)[0]['sum'];

			//获取总页数
			$maxPage = ceil($maxRows / $pageSize);

			//设置末页越界情况
			if($page > $maxPage){
				$page = $maxPage;
			}

			//设置首页越界情况
			if($page < 1){
				$page = 1;
			}

			//定义存储分页的变量
			$limit = '';

			//拼装分页语句
			$limit = ' limit '.(($page-1)*$pageSize).','.$pageSize;

	//=============================================
	
		
			//查询帖子（原生语句方法）
			$res = $post->query('select * from post where recycle=0 '.$where.$limit);

			//实例化user表
		    $user = new Model('user');

			require "./View/Login/index.html";
		}

		
		//用户登录
		public function doLogin()
		{
			//获取用户提交信息
			$userName = $_POST['userName'];
			$password = $_POST['password'];

			//实例化user表
			$user = new Model('user');

			//实例化userdetail表
			$ud = new Model('userdetail');

			//查看用户账号密码是否存在
			$res4 = $user->where("userName='{$userName}' && password='".md5($password)."'")->select();
	
			//确认登录
			if($res4){

				//获取用户状态
				$status = $res4[0]['status'];

				//判断用户状态是否可以登录
				if($status==0){

					echo "<script>alert('抱歉，您当前状态不允许登录，请联系管理员确认后重试！');window.location.href='./index.php?c=login&a=index'</script>";
					die;
				}

				//获取当前登录时间戳
				$lastlogin = time();

				//更新用户最近登录时间
				$res1 = $user->save(array(
									'id'=>$res4[0]['id'],
									'lastlogin'=>$lastlogin,
									));

				//增加用户积分+1
				$sorce = $user->query("update user set score=score+1 where userName='{$userName}'");

				//将登录成功用户的id存储session
				$_SESSION['uid'] = $res4[0]['id'];
			
				echo "<script>window.location.href='./index.php?c=index&a=index'</script>";
			}else{
				echo "<script>alert('抱歉，登录失败！');window.location.href='./index.php?c=login&a=index'</script>";
			}
			
		}

		//执行用户退出的方法
		public function doLogout()
		{
			//1.销毁session中的uid
			unset($_SESSION['uid']);

			//2.提示用户
			echo "<script>window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
		}
	}