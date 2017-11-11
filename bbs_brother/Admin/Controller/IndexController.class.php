<?php
	
	
	//创建一个主页控制器
	class IndexController
	{

		//加载主页
		public function index()
		{	
			//判断用户是否登陆
			
			if(isset($_SESSION['id'])){
				require "./View/Index/index.html";
			}else{
				echo "<script>alert('抱歉，您还没有登录，请先登录！');window.location.href='index.php?c=login&a=index'</script>";
				die;
			}
		}

		
	}