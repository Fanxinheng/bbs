<?php

	//创建一个详情页控制器
	class DetailController
	{
		//加载帖子详情主页
		public function index($pid=null)
		{	
			//判断是否传递id
			if(!$pid){

				//获取指定帖子的id
				$pid = $_GET['pid'];

			}
			
			//实例化post表
			$post = new Model('post');

			
			//查询帖子信息
			$res18 = $post->where('id='.$pid)->select();
			
			//拼装查询回复贴的条件
			$where = " where pid=".$pid;
		
		//================封装分页=====================
	
			//设置分页参数
			$page = isset($_GET['p'])?' '.$_GET['p']:'1';	//当前页码
			$maxRows = 0;	//总条数
			$pageSize = 3;	//每页条数
			$maxPage = 0;	//总页数

			//获取总条数
			$maxRows = $post->query("select count(*) as sum from reply".$where)[0]['sum'];

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
	
			//实例化reply表
			$reply = new Model('reply');

			//获取指定帖子的id
			$pid = $_GET['pid'];

			//查询指定帖子回复信息
			$res19 = $reply->query("select * from reply ".$where.$limit);

			require "./View/Detail/index.html";
		}

		//加载回复帖子主页面
		public function return()
		{
			//判断用户有没有登录
			if(!isset($_SESSION['uid'])){
				echo "<script>alert('抱歉，您还没有登录，快去登录享受更精彩的内容吧!');window.location.href='./index.php?c=login&a=index'</script>";
				die;
			}

			//实例化post表
			$post = new Model('post');

			//获取帖子id
			$id = $_GET['pid'];

			//查询指定帖子主题
			$title = $post->fields('title')->where('id='.$id)->select();

			require "./View/detail/return.html";
		}

		//执行回复指定功能
		public function doReturn()
		{
			//实例化reply表
			$reply = new Model('reply');

			//获取发帖内容信息
			$data['content'] = trim(strip_tags(htmlspecialchars_decode($_POST['content'])));

			//将发帖时间存入数组
			$data['ctime'] = time();

			//获取发帖人id
			$data['uid'] = $_SESSION['uid'];

			//获取当前帖子id
			$pid = $_GET['pid'];
			$data['pid'] = $pid;

			//获取回复前回复贴总数
			$count = $reply->query("select count(*) as num from reply where pid=".$pid);
			
			//将新发的帖子排名放入数组
			$data['num'] = $count[0]['num']+1;

			

			//执行回复帖子
			$res25 = $reply->add($data);
			


			//实例化user表
			$user = new Model('user');
			
			//获取用户姓名信息
			$userName = $user->fields('userName')->where('id='.$_SESSION['uid'])->select();
			
			
			//增加用户积分+10
			$score = $user->query("update user set score=score+5 where userName='{$userName[0]['userName']}'");

			if($res25 && $score){

				$this->index($pid);
			}else{
				echo "<script>alert('帖子回复失败，请重试！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
				die;
			}
		}

		//加载搜索帖子功能
		public function list_search()
		{
			
			//实例化post表
			$post = new Model('post');

			//分页判断搜索内容
			if(!empty($_REQUEST['search'])){

				$where = " where title like '%{$_REQUEST['search']}%' && recycle=0 ";

				//将搜索内容放入url地址
				$url = "&title=".$_REQUEST['search'];

			}else{

				$where = " where title like '%{$_GET['title']}%' && recycle=0 ";

				//获取url地址中的搜索信息
				$url = "&title=".$_GET['title'];

			}

			/*//获取查询信息
			$search = $_POST['search'];

			//拼装查询条件
			$where = " where title like '%$search%' && recycle=0 ";*/
	
	//================封装分页=====================
	
			//设置分页参数
			$page = isset($_GET['p'])?' '.$_GET['p']:'1';	//当前页码
			$maxRows = 0;	//总条数
			$pageSize = 5;	//每页条数
			$maxPage = 0;	//总页数

			//获取总条数
			$maxRows = $post->query("select count(*) as sum from post".$where)[0]['sum'];

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
			$res3 = $post->query('select * from post'.$where.' order by top desc,ctime desc '.$limit);
			

			//实例化user表
			$user = new Model('user');

			//引入文件
			require "./View/index/list_search.html";
		}
	}