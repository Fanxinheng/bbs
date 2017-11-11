<?php

	class RecycleController
	{
		//加载回收站主页面
		public function index()
		{
			//实例化post表
			$post = new Model('post');

	//=================封装搜索程序================

			//定义存储搜索信息的数组
			$whereList = array();
			$urlList = array();

			//获取查询帖子信息
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
			$maxRows = $post->query("select count(*) as sum from post where recycle=1".$where)[0]['sum'];

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
			$res = $post->query('select * from post where recycle=1 '.$where.$limit);
			
			//引入文件
			require "./View/Recycle/index.html";
		}

		//执行查看回收站帖子功能
		public function show()
		{
			//实例化post表
			$post = new Model('post');

			//获取查看帖子id
			$id = $_GET['id'];

			//查询表中的数据
			$res = $post->where("id=".$id)->select();
		
			//查询指定id的发帖人姓名
		    $user = new Model('user');

		    $name = $user->fields('userName')->where('id='.$res[0]['uid'])->select();
		    
			require "./View/recycle/show.html";
		}

		//加载编辑回收站帖子页面
		public function edit()
		{
			//实例化post表
			$post = new Model('post');

			//获取查看帖子id
			$id = $_GET['id'];

			//查询表中的数据
			$res = $post->where("id=".$id)->select();
		
			//查询指定id的发帖人姓名
		    $user = new Model('user');

		    $name = $user->fields('userName')->where('id='.$res[0]['uid'])->select();
		    
			require "./View/recycle/edit.html";
		}

		//执行编辑回收站帖子功能
		public function save()
		{
			//获取修改用户信息
			$data = $_POST;

			//实例化Model
			$post = new Model('post');

			//获取要修改用户的id
			$id = $_GET['id'];

			//将id放入数组
			$data['id'] = $id;

			//执行更新用户权限操作
			$res = $post->save($data);
			
			//判断是否修改成功
			if(!$res){
				echo "<script>alert('修改帖子失败！');window.location.href='./index.php?c=recycle&a=index'</script>";
				die;
			}else{
				echo "<script>window.location.href='./index.php?c=recycle&a=index'</script>";
				
			}
		}

		//删除回收站帖子的功能
		public function delete()
		{
			//实例化post表
			$post = new Model('post');

			//获取要删除帖子的id
			$id = $_GET['id'];

			//在post表删除帖子
			$res = $post->delete($id);

			//判断回收站帖子否删除成功
			if($res){
				$this->index();
			}else{
				echo "<script>alert('帖子删除失败！');</script>";
				$this->index();
				die;
			}
		}

		//帖子回收站恢复功能
		public function return()
		{
			//实例化post表
			$post = new Model('post');

			//获取帖子状态
			$recycle = $_GET['recycle'];

			//获取帖子主键id
			$id = $_GET['id'];

			//修改相应帖子状态
			switch($recycle){
				case "0":

					//如果原状态为0改为1
					$res = $post->save(array(
											'recycle'=>1,
											'id'=>$id,
											));
					break;

				case "1":
					//如果原状态为1改为0
					$res = $post->save(array(
											'recycle'=>0,
											'id'=>$id,
											));
					break;
			}
			if(!$res){
				echo "<script>alert('移出回收站失败！');window.location.href='./index.php?c=recycle&a=index'</script>";
			}else{
				$this->index();
			}
		}

	}