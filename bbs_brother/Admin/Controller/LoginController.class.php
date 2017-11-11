<?php
	

	//创建一个登陆控制器文件
	class LoginController
	{
		//加载登陆主页面
		public function index()
		{
			//判断是否已登陆
			if(isset($_SESSION['id'])){
				echo "<script>alert('抱歉，您已登陆，无需重复登录！');window.location.href='./index.php?c=index&a=index'</script>";
			}else{

				//加载登录主页页面
				require "./View/Login/index.html";
			}
		}

		//加载验证码
		public function codeimg()
		{
			//实例化验证码类
			$vcode = new Vcode();

			//生成验证码图像
			$vcode->doimg();

			//获取验证码内容
			$code = $vcode->getCode();
			
			//将验证码内容放进session
			$_SESSION['code'] = $code;
			
		}

		//执行登陆验证
		public function doLogin()
		{

			//将验证码转换为小写
			$code = strtolower($_POST['code']);
			
			//判断验证码是否正确
			if($code!=$_SESSION['code']){
				echo "<script>alert('验证码不正确，请重试！');window.location.href='./index.php?c=login&a=index'</script>";
				die;
			}

			//获取账号密码信息
			$userName = $_POST['userName'];
			$password = $_POST['password'];

			//实例化Model类
			$user = new Model('user');

			//查询账号密码是否存在
			$res = $user->where('userName="'.$userName.'" && password="'.md5($password).'"')->select();
			
			
			//判断是否查询成功
			if($res){

				//获取用户权限
				$auth = $res[0]['auth'];
		
				//判断用户权限是否允许登录
				if($auth!=0){

					//获取用户状态
					$status = $res[0]['status'];

					//判断用户状态是否允许登录
					if($status!=0){

						//将用户id放入session中
						$_SESSION['id'] = $res[0]['id'];

						echo "<script>window.location.href='./index.php?c=index&a=index'</script>";
					}else{

						echo "<script>alert('抱歉，您当前状态不允许登录，请联系管理员确认后重新登录！');window.location.href='./index.php?c=login&a=index'</script>";
						die;
					}
				}else{
					echo "<script>alert('抱歉，您没有权限登录！');window.location.href='./index.php?c=login&a=index'</script>";
					die;
				}
				
			}else{
				echo "<script>alert('抱歉，用户名或密码错误！');window.location.href='./index.php?c=login&a=index'</script>";
				die;
			}

		}

		//用户注销功能
		public function doLogout()
		{
			//销毁session中的数据
			unset($_SESSION['id']);

			//销毁session文件
			session_destroy();

			//销毁客户端的cookie信息
			setcookie('PHPSESSID','',time()-1,'/');

			echo "<script>alert('恭喜，退出成功！');window.location.href='./index.php?c=login&a=index'</script>";
		}

	}