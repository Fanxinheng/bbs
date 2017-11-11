<?php

	//创建一个注册页的控制器
	class RegisterController
	{
		//加载注册主页
		public function index()
		{
			require "./View/Register/index.html";
		}

		//加载注册功能
		public function doRegister()
		{
			//判断数据是否为空
			if(empty($_POST['userName']) || empty($_POST['password']) || empty($_POST['surepass']) || empty($_POST['email'])){
			echo "<script>alert('抱歉，不能提交空数据！');window.location.href='./index.php?c=register&a=index'</script>";
			die;
			}

			//判断两次密码是否一致
			if($_POST['password'] != $_POST['surepass']){
				echo "<script>alert('抱歉，两次密码不一致！');window.location.href='./index.php?c=register&a=index'</script>";
				die;
			}

			//判断注册用户是否已经存在
			$user = new Model('user');
			$res = $user->where("userName='{$_POST['userName']}'")->select();

			if($res){
				echo "<script>alert('抱歉，用户名已被占用！');window.location.href='./index.php?c=register&a=index'</script>";
				die;
			}

			//获取注册用户信息
			$userName = $_POST['userName'];
			$password = $_POST['password'];

			//定义一个用于存储的新数组
			$data = array();

			//存储用户信息
			$data['userName'] = $userName;
			$data['password'] = md5($password);
			$data['lastlogin'] = time();

			//将信息添加到user表
			$id = $user->add($data);

			if($id){

				//获取信息添加到userdetail
				$ndata = array();
				$email = $_POST['email'];
				$ndata['uid'] = $id;
				$ndata['email'] = $email;

				//实例化userdetail表
				$ud = new Model('userdetail');

				//将信息添加到userdeta表
				$nid = $ud->add($ndata);

				//判断是否添加成功
				if($nid){
					echo "<script>window.location.href='./index.php?c=login&a=index'</script>";
				}else{
					echo "<script>alert('抱歉，用户注册失败！');window.location.href='./index.php?c=register&a=index'</script>";
				}
			}else{
				echo "<script>alert('用户注册失败！请稍后重试！');window.location.href='./index.php?c=register&a=index'</script>";
				die;
			}
		}
	}