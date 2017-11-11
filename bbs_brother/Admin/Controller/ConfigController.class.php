<?php

	//创建一个网站配置控制器
	class ConfigController
	{
		//加载网络配置主页面
		public function index()
		{	
			//实例化Model类
			$con = new Model('config');
			
			//获取用户信息
			$res = $con->select();

			require "./View/Config/index.html";
		}

		//执行网站配置修改
		public function save()
		{	

			//实例化Model类
			$con = new Model('config');

			//定义一个存储配置的新数组
			$data = array();

			//获取网站配合信息
			$data = $_POST;
			$data['id'] = 1;
			$logo = $_FILES['logo']['name'];
		

			//判断是否修改logo
			if($logo!=''){

		//============== 上传logo  ========================
		
				//实例化文件上传类
				$upload = new Fileupload();

				//定义上传文件必备的参数
				$upload->set('path','./Public/logo');
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

				//获取要修改网站原logo
				$res = $con->fields('logo')->where('id=1')->select();
				$oldLogo = $res[0]['logo'];

		//=================================================

				//将logo名称存入数组
				$data['logo'] = $imgName;
				
				//执行配置修改功能
				$res = $con->save($data);

				//判断是否修改成功
				if(!$res){
					echo "<script>alert('网站配置修改失败！');window.location.href='./index.php?c=config&a=index'</script>";
					die;
				}else{

					//删除原logo图像
					unlink("./Public/logo/".$oldLogo);

					$this->index();
				}
			}else{

				//执行配置修改功能
				$res = $con->save($data);

				//判断是否修改成功
				if(!$res){
					echo "<script>alert('网站配置修改失败！');window.location.href='./index.php?c=config&a=index'</script>";
					die;
				}else{

					$this->index();
				}
			}
		}
	}