<?php

	//创建一个修改头像的控制器
	class ImageController
	{

		//加载修改主页
		
		public function index()
		{
			require "./View/Center/image.html";
		}
		//加载用户头像修改页
		public function saveImg()
		{	

			//实例化文件上传类
			$upload = new Fileupload();


			//定义上传文件必备的参数
			$upload->set('path','./Public/uploads');
			$upload->set('allowtype',array('jpg','gif','png'));
			$upload->set('maxsize',999999999);
			$upload->set('israndname',true);
			
			//执行上传文件
			$res = $upload->upload('pic');
			
			
			//判断是否上传成功
			if(!$res){
			   $res1 = $upload->getErrorMsg();
				echo $res1;
				die;
			}
			//获取图片名称
			$imgName = $upload->getFileName();

			//对图片进行缩放处理

			//实例化对象
			$img = new Image($path="./Public/uploads");

			//对头像进行缩放并获取名称
			$newName = $img->thumb($imgName,100,100,'S_');
			
			//实例化Model类
			$user = new Model('userdetail');

			//获取要修改用户的ID
			$res = $user->fields('id')->where('uid='.$_SESSION['uid'])->select();
			$id = $res[0]['id'];
			
			//获取要修改用户的原缩略头像
			$res = $user->fields('photo')->where('uid='.$_SESSION['uid'])->select();
			$photo = $res[0]['photo'];
			

			//获取要修改用户的原头像
			$res = $user->fields('oldPhoto')->where('uid='.$_SESSION['uid'])->select();
			$oldPhoto = $res[0]['oldPhoto'];

/*			//判断用户有没有上传新的头像
			if($newName=''){
				echo "<script>alert('抱歉，您没有上传新头像！');window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
				die;
			}
*/			

			//拼装要执行的修改信息
			$data = array(
						'photo'=>$newName,
						'oldPhoto'=>$imgName,
						'id'=>$id,
						);

			
			//执行修改头像
			$res1 = $user->save($data);

			//判断是否修改成功
			if($res1){
				
				//判断要修改是不是默认头像
				if($photo != 'default.jpg'){
					//销毁非默认原头像
					unlink("./Public/uploads/".$photo);
					unlink("./Public/uploads/".$oldPhoto);
				}

				echo "<script>window.location.href='{$_SERVER['HTTP_REFERER']}'</script>";
			}
		
		}
	}