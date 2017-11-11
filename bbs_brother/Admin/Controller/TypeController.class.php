<?php
	
	//创建一个版块控制器
	class TypeController
	{
		//加载浏览分区页面
		public function index()
		{
			//实例化Type表
			$type = new Model('Type');

			//查询表中的数据
			$res = $type->query("select *,concat(path,'-',id) as npath from type order by npath;");

			//引入文件
			require "./View/Type/index.html";
		}

		//加载添加父分区页面
		public function addFather()
		{
			require "./View/Type/addFather.html";
		}

		//添加父分区方法
		public function insert()
		{
			//获取父分区名称
			$father = $_POST['father'];

			//实例化Model类
			$type = new Model('type');

			//执行添加功能
			$id = $type->query("insert into type (name) value ('{$father}')");

			//判断是否添加成功
			if($id){
				echo "<script>window.location.href='./index.php?c=type&a=index'</script>";
			}else{
				echo "<script>alert('抱歉，父分区添加失败！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
			}
		}

		//加载添加子版块页面
		public function addSon()
		{
			require "./View/Type/addSon.html";
		}

		//添加子版块的方法
		public function insertSon()
		{
			//实例化Model类
			$type = new Model('type');

			//获取父类id
			$pid = $_GET['fid'];

			//获取添加子版块的名称
			$name = $_POST['son'];

			//执行添加子版块功能
			$id = $type->query("insert into type (name,pid,path) values ('{$name}',{$pid},'0-{$pid}')");

			//判断是否添加成功
			if(!$id){
				echo "<script>alert('抱歉，子版块添加失败！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
			}

			//定义一个存储子版块的新数组
			$data = array();

		//============== 上传logo  ========================
	
			//实例化文件上传类
			$upload = new Fileupload();

			//定义上传文件必备的参数
			$upload->set('path','./Public/bLogo');
			$upload->set('allowtype',array('jpg','gif','png'));
			$upload->set('maxsize',999999999);
			$upload->set('israndname',true);
			
			//执行上传文件
			$res = $upload->upload('blogo');
			
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
			$data['blogo'] = $imgName;
			
			//将插入子版块的id放入数组
			$data['id'] = $id;

			//执行更新子版块logo
			$res = $type->query("update type set blogo='".$imgName."' where id=".$id);

			//判断是否添加成功
			if(!$res){
				echo "<script>alert('抱歉，子版块logo添加失败！');window.location.href='./index.php?c=type&a=index'</script>";
				die;
			}else{
				$this->index();
			}
		}

		//加载修改父分区主页
		public function edit()
		{	
			//实例化Type表
			$type = new Model('Type');

			//获取编辑用户id
			$id = $_GET['id'];
			
			//获取用户信息
			$res = $type->where('id='.$id)->select();
			
			require "./View/Type/edit.html";
		}

		//执行修改父分区的功能
		public function save()
		{
			//获取修改用户信息
			$name = $_POST['name'];
			$status = $_POST['status'];

			//实例化Model
			$type = new Model('type');

			//获取要修改用户的id
			$id = $_GET['id'];

			//执行更新用户权限操作
			$res = $type->save(array(
									'name'=>$name,
									'status'=>$status,
									'id'=>$id
									));
			
			//判断是否修改成功
			if(!$res){
				echo "<script>alert('修改父分区失败！');window.location.href='./index.php?c=user&a=index'</script>";
				die;
			}else{
				$this->index();
			}
		}

		//执行父分区删除功能
		public function delete()
		{
			//实例化type表
			$type = new Model('type');

			//获取要删除用户的id
			$id = $_GET['id'];

			//查询父分区中子版块是否有内容
			$res = $type->fields('name')->where('pid='.$id)->select();
			
			if($res){
				echo "<script>alert('抱歉，删除失败，此分区中含有其他子版块！');window.location.href='./index.php?c=type&a=index'</script>";
				die;
			}
			
			//执行删除功能
			$res1 = $type->delete($id);

			//判断是否删除成功
			if($res1){
				$this->index();
			}else{
				echo "<script>alert('删除分区失败！');window.location.href='./index.php?c=type&a=index'</script>";
			}
		}


		//加载修改子版块主页
		public function editSon()
		{	
			//实例化Type表
			$type = new Model('Type');

			//获取编辑用户id
			$id = $_GET['id'];
			
			//获取用户信息
			$res = $type->where('id='.$id)->select();
			
			require "./View/Type/editSon.html";
		}

		//执行子版块修改功能
		public function saveSon()
		{	

			//实例化type表
			$type = new Model('type');

			//定义一个存储链接信息的新数组
			$data = array();

			//获取链接信息
			$data = $_POST;
			$data['id'] = $_GET['id'];
			$logo = $_FILES['blogo']['name'];
			
			//判断是否修改logo
			if($logo!=''){

		//============== 上传logo  ========================
		
				//实例化文件上传类
				$upload = new Fileupload();

				//定义上传文件必备的参数
				$upload->set('path','./Public/blogo');
				$upload->set('allowtype',array('jpg','gif','png'));
				$upload->set('maxsize',999999999);
				$upload->set('israndname',true);
				
				//执行上传文件
				$res = $upload->upload('blogo');
				
				//判断是否上传成功
				if(!$res){
				   $res1 = $upload->getErrorMsg();
					echo $res1;
					die;
				}

				//获取图片名称
				$imgName = $upload->getFileName();

				//获取要修改链接原logo
				$res = $type->fields('blogo')->where('id='.$_GET['id'])->select();
				$oldLogo = $res[0]['blogo'];

		//=================================================

				//将新logo名称存入数组
				$data['blogo'] = $imgName;
				
				//执行配置修改功能
				$res = $type->save($data);

				//判断是否修改成功
				if(!$res){
					echo "<script>alert('版块修改失败！');window.location.href='./index.php?c=type&a=index'</script>";
					die;
				}else{

					//判断要修改是否为默认logo
					if($oldLogo!='default.jpg'){

						//删除原logo图像
						unlink("./Public/blogo/".$oldLogo);
					}
					$this->index();
				}
			}else{

				//执行配置修改功能
				$res = $type->save($data);

				//判断是否修改成功
				if(!$res){
					echo "<script>alert('版块修改失败！');window.location.href='./index.php?c=type&a=index'</script>";
					die;
				}else{
					$this->index();
				}
			}
		}


		//执行删除子版块功能
		public function deleteSon()
		{
			//实例化post表
			$post = new Model('post');

			//获取要删除版块的id
			$id = $_GET['id'];

			//查询父分区中子版块是否有内容
			$res = $post->where('tid='.$id)->select();
			
			if($res){
				echo "<script>alert('抱歉，删除失败，此版块含有用户帖子！');window.location.href='./index.php?c=type&a=index'</script>";
				die;
			}
			
			//实例化type表
			$type = new Model('type');

			//执行删除功能
			$res1 = $type->delete($id);

			//判断是否删除成功
			if($res1){
				$this->index();
			}else{
				echo "<script>alert('删除版块失败！');window.location.href='./index.php?c=type&a=index'</script>";
			}
		}


		//加载指定版块帖子列表主页面
		public function show()
		{
			//实例化post表
			$post = new Model('post');

			//获取版块tid
			$tid = $_GET['tid'];

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
				$url = "& ".implode('&',$urlList);
			}
	//=============================================
	
	//================封装分页=====================
	
			//设置分页参数
			$page = isset($_GET['p'])?$_GET['p']:'1';	//当前页码
			$maxRows = 0;	//总条数
			$pageSize = 5;	//每页条数
			$maxPage = 0;	//总页数

			//获取总条数
			$maxRows = $post->query("select count(*) as sum from post where recycle=0 && tid=".$tid.$where)[0]['sum'];

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
			$res30 = $post->query('select * from post where recycle=0  &&tid='.$tid.$where.$limit);

			//引入文件
			require "./View/type/show.html";
		}

		

	}