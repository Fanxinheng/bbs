<?php


	//创建一个友情链接控制器
	class LinkController
	{
		//加载浏览链接页面
		public function index()
		{

			//实例化Model类
			$link = new Model('friendlink');

	//=================封装搜索程序================

			//定义存储搜索信息的数组
			$whereList = array();
			$urlList = array();

			//获取查询的姓名信息
			if(!empty($_REQUEST['linkname'])){
				$whereList[] = " linkname like '%{$_REQUEST['linkname']}%'";
				$urlList[] = "linkname={$_REQUEST['linkname']}";
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
			$maxRows = $link->query("select count(*) as sum from friendlink".$where)[0]['sum'];

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
			$res = $link->query('select * from friendlink'.$where.$limit);

			require "./View/Link/index.html";
		}

		//加载添加链接页面
		public function add()
		{
			require "./View/Link/add.html";
		}

		//执行添加链接功能
		public function insert()
		{
			//实例化Model
			$link = new Model('friendlink');

			//定义一个存储配置的新数组
			$data = array();

			//获取网站配合信息
			$data = $_POST;

		//============== 上传logo  ========================
		
				//实例化文件上传类
				$upload = new Fileupload();

				//定义上传文件必备的参数
				$upload->set('path','./Public/linkLogo');
				$upload->set('allowtype',array('jpg','gif','png'));
				$upload->set('maxsize',999999999);
				$upload->set('israndname',true);
				
				//执行上传文件
				$res = $upload->upload('logo');
			
				//判断是否上传成功
				if(!$res){
				   $res1 = $upload->getErrorMsg();
					echo $res1;
					die;
				}
				//获取图片名称
				$imgName = $upload->getFileName();

		//=================================================
				//将logo名称存入数组
				$data['logo'] = $imgName;
				
				//执行添加链接
				$res = $link->add($data);

				//判断是否添加成功
				if(!$res){
					echo "<script>alert('友情链接添加失败！');window.location.href='./index.php?c=link&a=index'</script>";
					die;
				}else{

					echo "<script>window.location.href='./index.php?c=link&a=index'</script>";
				}
		}

		//加载修改链接的主页面
		public function edit()
		{	
		//============选出要修改的用户信息=============
			
			//实例化Model
			$link = new Model('friendlink');

			//获取编辑用户id
			$id = $_GET['id'];
			
			//获取用户信息
			$res = $link->where('id='.$id)->select();

			
		//=============================================
			
			require "./View/Link/edit.html";
		}

		//执行友情连接修改
		public function save()
		{	

			//实例化Model类
			$link = new Model('friendlink');

			//定义一个存储链接信息的新数组
			$data = array();

			//获取链接信息
			$data = $_POST;
			$data['id'] = $_GET['id'];
			$logo = $_FILES['logo']['name'];
			
			//判断是否修改logo
			if($logo!=''){

		//============== 上传logo  ========================
		
				//实例化文件上传类
				$upload = new Fileupload();

				//定义上传文件必备的参数
				$upload->set('path','./Public/linkLogo');
				$upload->set('allowtype',array('jpg','gif','png'));
				$upload->set('maxsize',999999999);
				$upload->set('israndname',true);
				
				//执行上传文件
				$res = $upload->upload('logo');
				
				//判断是否上传成功
				if(!$res){
				   $res1 = $upload->getErrorMsg();
					echo $res1;
					die;
				}
				//获取图片名称
				$imgName = $upload->getFileName();

				//获取要修改链接原logo
				$res = $link->fields('logo')->where('id='.$_GET['id'])->select();
				$oldLogo = $res[0]['logo'];

		//=================================================

				//将logo名称存入数组
				$data['logo'] = $imgName;
				
				//执行配置修改功能
				$res = $link->save($data);

				//判断是否修改成功
				if(!$res){
					echo "<script>alert('友情连接修改失败！');window.location.href='./index.php?c=link&a=index'</script>";
					die;
				}else{

					//删除原logo图像
					unlink("./Public/linkLogo/".$oldLogo);

					echo "<script>window.location.href='./index.php?c=link&a=index'</script>";
				}
			}else{

				//执行配置修改功能
				$res = $link->save($data);

				//判断是否修改成功
				if(!$res){
					echo "<script>alert('链接修改失败！');window.location.href='./index.php?c=link&a=index'</script>";
					die;
				}else{

					echo "<script>window.location.href='./index.php?c=link&a=index'</script>";
				}
			}
		}


		//执行删除友情连接的功能
		public function delete()
		{
			//实例化Model
			$link = new Model('friendlink');
			
			//获取要删除链接的id
			$id = $_GET['id'];

			//获取删除链接logo名称
			$logo = $_GET['logo'];

			//执行删除
			$res = $link->delete($id);

			//判断链接是否删除成功
			if($res){

				//删除链接logo
				unlink("./Public/linkLogo/".$logo);
				$this->index();
			}else{
				echo "<script>alert('删除链接失败！');window.location.href='./index.php?c=link&a=index'</script>";
			}
		}
	}