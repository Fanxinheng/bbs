<?php
	
	//创建一个主页控制器
	class IndexController
	{
		//加载主页页面
		public function index()
		{	
			//实例化type表
			$type = new Model('type');

			//查询版块信息
			$res1 = $type->select();

			//实例化config表
			$con = new Model('config');

			//查询网站配置信息
			$res2 = $con->select();


			//引入主页文件
			require "./View/Index/index.html";
		}

		//加载前台帖子列表主页
		public function list($tid=null)
		{
			if(!isset($tid)){

			//获取分区id
			$tid = $_GET['tid'];
			}

			//实例化post表
			$post = new Model('post');


			//拼装查询条件
			$where = ' where recycle=0 && tid='.$tid;
	
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

			//实例化type表
			$type = new Model('type');

			//查询分区名称
			$typeN = $type->where('id='.$tid)->select();

			//引入文件
			require "./View/index/list.html";
		}

		//加载发帖主页面
		public function send()
		{
			//判断用户有没有登录
			if(!isset($_SESSION['uid'])){
				echo "<script>alert('抱歉，您还没有登录，快去登录享受更精彩的内容吧!');window.location.href='./index.php?c=login&a=index'</script>";
				die;
			}
			require "./View/index/send.html";
		}

		//执行发帖功能
		public function doSend()
		{
			//实例化post表
			$post = new Model('post');

			//获取发帖内容信息
			$data['title'] = $_POST['title'];
			$data['content'] = trim(strip_tags(htmlspecialchars_decode($_POST['content'])));

			//发帖是否允许回复
			$data['replay'] = $_POST['replay'];

			//将发帖时间存入数组
			$data['ctime'] = time();

			//获取发帖人id
			$data['uid'] = $_SESSION['uid'];

			//获取当前版块id
			$tid = $_GET['tid'];
			$data['tid'] = $tid;

			//执行添加帖子
			$res17 = $post->add($data);

			//实例化user表
			$user = new Model('user');
			
			//获取用户姓名信息
			$userName = $user->fields('userName')->where('id='.$_SESSION['uid'])->select();
			
			
			//增加用户积分+10
			$score = $user->query("update user set score=score+10 where userName='{$userName[0]['userName']}'");

			
			if($res17 && $score){

				//传递分区tid
				$this->list($tid);
			}else{
				echo "<script>alert('帖子发表失败，请重试！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
				die;
			}
		}

	}