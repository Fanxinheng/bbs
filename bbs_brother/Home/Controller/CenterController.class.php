<?php

	//创建一个个人中心控制器
	class CenterController
	{
		//加载个人中心主页
		public function index()
		{
			require "./View/Center/index.html";
		}

		//加载个人信息修改功能
		public function save()
		{
			//获取修改的信息
			$data['nickName'] = $_POST['nickName'];
			$data['email'] = $_POST['email'];
			$data['qq'] = $_POST['qq'];
			$data['sex'] = $_POST['sex'];
			

			//实例化userdetail表
			$ud = new Model('userdetail');

			//实例化user表
			$user = new Model('user');

			//判断密码是否为空
			if($_POST['password']==''){

				//查询要修改的用户信息
				$res = $ud->where('uid='.$_SESSION['uid'])->select();

				//获取修改用户id
				$data['id'] = $res[0]['id'];

				//执行userde表修改
				$result = $ud->save($data);

				//判断是否修改成功
				if($result){
					echo "<script>window.location.href='index.php?c=index&a=index'</script>";
					
				}else{
					echo "<script>alert('抱歉，修改失败！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
					die;
				}
					
			}else{

				//查询要修改的用户信息
				$res = $ud->where('uid='.$_SESSION['uid'])->select();

				//获取修改用户id
				$data['id'] = $res[0]['id'];

				//执行userde表修改
				$result = $ud->save($data);

				//获取user表中id
				$data1['id'] = $res[0]['uid'];

				//获取要修改的密码
				$data1['password'] = md5($_POST['password']);

				//执行user表修改
				$result1 = $user->save($data1);

				//判断密码是否修改成功
				if($result1 && $result1){
					echo "<script>window.location.href='index.php?c=index&a=index'</script>";
				}else{
					echo "<script>alert('抱歉，个人信息修改失败！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
					die;
				}

			}
			
		}

		
	}