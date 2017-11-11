<?php
	

	//创建一个用户控制器文件
	class UserController
	{
		//加载用户展示页面
		public function index()
		{
			//实例化Model类
			$user = new Model('user');

	//=================封装搜索程序================

			//定义存储搜索信息的数组
			$whereList = array();
			$urlList = array();

			//获取查询的姓名信息
			if(!empty($_REQUEST['userName'])){
				$whereList[] = " userName like '%{$_REQUEST['userName']}%'";
				$urlList[] = "userName={$_REQUEST['userName']}";
			}

			//定义存储搜素语句的变量
			$where = "";
			$url = "";

			//判断搜索条件是否存在
			if(!empty($whereList)){

				//拼装查询条件
				$where = " where ".implode(' && ',$whereList);

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
			$maxRows = $user->query("select count(*) as sum from user".$where)[0]['sum'];

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
	
		
			//查询用户（原生语句方法）
			$res = $user->query('select * from user'.$where.$limit);

			//权限列表
			$auth = array('普通用户','一般管理员','超级管理员');

			//引入文件
			require "./View/User/index.html";
		}

		//加载用户添加页面
		public function add()
		{
			require "./View/User/add.html";
		}

		//执行用户添加到数据库
		public function insert()
		{
			//实例化user表
			$user = new Model('user');

			//获取要添加的数据
			$userName = $_POST['name'];

			//判断确认密码是否一致
			if($_POST['pwd'] != $_POST['upwd']){
				echo "抱歉，两次密码输入不一致，请重试！";
				die;
			}else{
				$password = md5($_POST['pwd']);
			}

			//获取用户权限
			$auth = $_POST['auth'];
			
			$data = array(
						'userName'=>$userName,
						'password'=>$password,
						'auth'=>$auth,
						);

			//执行用户添加
			$res1 = $user->add($data);


			//实例化userdetail表
			$ud = new Model('userdetail');

			//获取添加用户的id
			$id = $user->fields('id')->where("userName='".$userName."'")->select();

			
			$id = $id[0]['id'];

			//将id信息拼装
			$data = array(
						'uid'=>$id,
						);

			//将id插入到userdetail表
			$res2 = $ud->add($data);

			//判断是否添加成功
			if($res1 && $res2){
				$this->index();
			}else{
				echo "<script>alert(抱歉，添加用户失败！);</script>";
				die;
			}

		}


		//加载修改用户的页面
		public function edit()
		{	
		//============选出要修改的用户信息=============
			
			//实例化Model
			$user = new Model('user');

			$ud = new Model('userdetail');
			//获取编辑用户id
			$id = $_GET['id'];
		
			//获取用户信息
			$res = $user->where('id='.$id)->select();

			//判断用户权限是否可以修改
			if($res[0]['auth']==2){
				echo "<script>alert('抱歉，该用户为超级管理员，您无权修改！');window.location.href='./index.php?c=user&a=index'</script>";
				die;
			}

			$res1 = $ud->where('uid='.$id)->select();
			


		//=============================================
			
			require "./View/User/edit.html";
		}

		//修改用户的功能
		public function save()
		{
			//获取修改用户信息
			$auth = $_POST['auth'];

			//实例化Model
			$user = new Model('user');

			//获取要修改用户的id
			$id = $_GET['id'];

			//执行更新用户权限操作
			$res = $user->save(array(
									'auth'=>$auth,
									'id'=>$id
									));
			
			//判断是否修改成功
			if(!$res){
				echo "<script>alert('用户权限没有更改！');window.location.href='./index.php?c=user&a=index'</script>";
				die;
			}else{
				$this->index();
			}
		}

		//重置用户密码为默认123456功能
		public function resetpwd()
		{	
			//将用户密码设置为123456
			$password = 123456;

			//实例化Model
			$user = new Model('user');
			$ud = new Model('userdetail');


			//获取要修改用户的id
			$id = $_GET['id'];

		//=====判断默认密码是否与原密码重复======
			
			//获取原密码
			$res = $user->fields('password')->where('id='.$id)->select();
		
			//判断是否一致
			if(md5($password)==$res[0]['password']){
				echo "<script>alert('默认密码与用户原密码一致！');window.location.href='./index.php?c=user&a=index'</script>";
				die;
			}
		//=======================================
	
			
			//将默认密码更新到数据表
			$res = $user->query('update user set password="'.md5($password).'" where id='.$id );
			
		
			
			//判断是否重置成功
			if($res){
				$this->index();
			}else{
				echo "<script>alert('重置用户密码失败！');window.location.href='./index.php?c=user&a=index'</script>";
			}
		}

		//删除用户的功能
		public function delete()
		{
			//实例化Model
			$user = new Model('user');
			$ud = new Model('userdetail');

			//获取要删除用户的id
			$id = $_GET['id'];

			//查询要删除用户权限
			$auth = $user->fields('auth')->where('id='.$id)->select();
			
			//判断用户权限是否可以删除
			if($auth[0]['auth']==2){
				echo "<script>alert('抱歉，该用户为超级管理员，您无权删除！');window.location.href='./index.php?c=user&a=index'</script>";
				die;
			}

			//在user表删除用户
			$res = $user->delete($id);

			//在userdetail表删除用户
			$res1 = $ud->query("delete from userdetail where uid=".$id);
			
			//判断两个表是否删除成功
			if($res && $res1){
				$this->index();
			}else{
				echo "<script>alert('删除用户失败！');window.location.href='./index.php?c=user&a=index'</script>";
			}
		}

		//禁用用户功能
		public function no()
		{
			//实例化Model
			$user = new Model('user');

			//获取原用户状态
			$status = $_GET['status'];

			//获取原用户主键id
			$id = $_GET['id'];

			//查询要删除用户权限
			$auth = $user->fields('auth')->where('id='.$id)->select();
			
			//判断用户权限是否可以删除
			if($auth[0]['auth']==2){
				echo "<script>alert('抱歉，该用户为超级管理员，您无权禁用！');window.location.href='./index.php?c=user&a=index'</script>";
				die;
			}
			
			//修改相应用户状态
			switch($status){
				case "0":

					//如果原状态为0改为1
					$res = $user->save(array(
											'status'=>1,
											'id'=>$id,
											));
					break;

				case "1":
					//如果原状态为1改为0
					$res = $user->save(array(
											'status'=>0,
											'id'=>$id,
											));
					break;
			}
			if(!$res){
				echo "<script>alert('状态修改失败！');window.location.href='./index.php?c=user&a=index'</script>";
			}else{
				$this->index();
			}
		}
	}