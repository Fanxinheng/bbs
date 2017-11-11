<?php
	//封装Model类
	class Model
	{
		//成员属性
		public $tableName;		//存储表名
		private $link;			//存储数据库服务器对象
		private $pk;			//存储主键信息
		private $field;			//存储除了主键外的字段信息
		private $fields;		//存储查询多条时的字段信息
		private $where;			//存储查询多条时的条件信息
		private $order;			//存储查询多条时的排序信息
		private $limit;			//存储查询多条时的分页信息

		//构造方法
		public function __construct($tableName)
		{
			//给表名赋初值
			$this->tableName = $tableName;

			//1.连接数据库服务器，并判断是否成功
			$link = mysqli_connect(HOST,USER,PASS) or die('数据库链接失败！');

			//2.设置字符集
			mysqli_set_charset($link,CHARSET);

			//3.选择数据库
			mysqli_select_db($link,DBNAME);

			$this->link = $link;

			//调用获取所有字段信息及主键信息的方法
			$this->getAllFields();

		}

		//获取所有字段信息和主键信息的方法
		public function getAllFields()
		{
			//获取表中的所有字段信息
			$sql = 'desc '.$this->tableName;
			$result = mysqli_query($this->link,$sql);

			//解析结果集
			$d = array();	//存储除了主键外的所有字段名
			$pk = '';		//存储主键信息
			while($rows = mysqli_fetch_assoc($result)){
				
				//判断是否为逐渐
				if($rows['Key']=='PRI'){
					$pk = $rows['Field'];
				}else{
					$d[] = $rows['Field'];
				}
				
			}
			//将字段信息和主键信息存储到成员属性当中
			$this->pk = $pk;
			$this->field = $d;
		}

		//过滤字段信息的方法
		public function filterData($data)
		{
			foreach($data as $k=>$v){

				//判断是否在表字段数组中
				if(!in_array($k,$this->field) && $k!=$this->pk){
					//判断权限
					if(AUTH==1){
						unset($data[$k]);
					}
					if(AUTH==2){
						die('抱歉，您所传递的信息中含有非法字段：'.$k);
					}
				}
			}
			return $data;
		}

		//添加的方法
		//封装添加的方法
		//将   array('name'=>'张三','sex'=>'男','age'=>20);
		//				||
		//				||
		//				\/
		//变为 insert into student (name,sex,age) values ('张三','男',20);
		public function add($data)
		{

			//过滤字段信息
			$data = $this->filterData($data);

			//封装字段的sql语句
			$keys = array_keys($data);
			$keys_sql = implode(',',$keys);

			//封装值的sql语句
			$values = array_values($data);
			$values_sql = '"'.implode('","',$values).'"';

			//4.定义sql语句
			$sql = 'insert into '.$this->tableName.' ('.$keys_sql.') values ('.$values_sql.');';
			
			$bool = mysqli_query($this->link,$sql);

			//5.判断
			if($bool!=false && mysqli_affected_rows($this->link)>0){
				return mysqli_insert_id($this->link);
			}else{
				return false;
			}
		}

		//删除的方法
		public function delete($id)
		{
			//定义删除的sql语句
			$sql = 'delete from '.$this->tableName.' where '.$this->pk.' = '.$id;
			$bool = mysqli_query($this->link,$sql);

			//判断是否删除成功
			if($bool!=false && mysqli_affected_rows($this->link)>0){
				return mysqli_affected_rows($this->link);
			}else{
				return false;
			}
		}

		//修改的方法
		public function save($data)
		{
			//过滤字段信息
			$data = $this->filterData($data);

			//遍历数组，进行数据拼装
			$set = '';
			$where = '';
			foreach($data as $k=>$v){
				if($k==$this->pk){
					$where = $this->pk.' = '.$v;
				}else{
					$set .= $k.'="'.$v.'"'.',';
				}
				
			}

			//将字段信息修改之后的逗号去除
			$set = rtrim($set,',');

			//判断是否传递了id信息
			if($where==''){
				die('修改执行失败！原因：没有传递要修改信息的id号！');
			}

			//定义修改的sql语句
			$sql = 'update '.$this->tableName.' set '.$set.' where '.$where.';';
			$bool = mysqli_query($this->link,$sql);

			//判断是否修改成功
			if($bool!=false && mysqli_affected_rows($this->link)>0){
				return mysqli_affected_rows($this->link);
			}else{
				return false;
			}
		}

		//查询单条信息的方法
		public function find($id)
		{
			//定义查询的sql语句
			$sql = 'select * from '.$this->tableName.' where '.$this->pk.' = '.$id.';';
			$result = mysqli_query($this->link,$sql);

			//判断是否成功
			if($result!=false && mysqli_num_rows($result)>0){
				
				//解析结果集
				$row = mysqli_fetch_assoc($result);
				return $row;
			}else{
				return false;
			}
		}

		//查询多条信息的方法
		public function select()
		{
			//判断是否给定字段信息
			$f = '';
			if($this->fields){
				$f = $this->fields;
			}else{
				$f = '*';
			}

			//判断是否给定查询信息
			$w = '';
			if($this->where){
				$w = ' where '.$this->where;
			}

			//判断是否给定排序信息
			$o = '';
			if($this->order){
				$o = ' order by '.$this->order;
			}

			//判断是否给定分页信息
			$l = '';
			if($this->limit){
				$l = ' limit '.$this->limit;
			}

			//定义查询多条的sql语句
			$sql = 'select '.$f.' from '.$this->tableName.' '.$w.' '.$o.' '.$l.';';
			$result = mysqli_query($this->link,$sql);

			//清空搜索条件信息
			$this->clearCondition();

			//判断
			if($result!=false && mysqli_num_rows($result)>0){

				//解析结果集
				$data = array();
				while($rows = mysqli_fetch_assoc($result)){
					$data[] = $rows;
				}

				//返回查询结果
				return $data;
			}else{
				return false;
			}
		}

		//封装清空搜索信息的方法
		public function clearCondition()
		{
			//清空查询条件的信息（为了下次查询数据是正确的）
			$this->fields = '';
			$this->where = '';
			$this->order = '';
			$this->limit = '';
		}

		//封装存储字段信息的方法
		public function fields($fields)
		{
			$this->fields = $fields;
			return $this;
		}

		//封装查询条件信息的方法
		public function where($where)
		{
			$this->where = $where;
			return $this;
		}

		//封装排序条件信息的方法
		public function order($order)
		{
			$this->order = $order;
			return $this;
		}

		//封装分页条件信息的方法
		public function limit($limit)
		{
			$this->limit = $limit;
			return $this;
		}

		//统计的方法
		public function count()
		{
			//定义统计数量的sql语句
			$sql = 'select count(*) sum from '.$this->tableName.';';
			$result = mysqli_query($this->link,$sql);

			//判断是否查询成功
			if($result!=false && mysqli_num_rows($result)>0){

				//解析结果集
				$row = mysqli_fetch_assoc($result)['sum'];
				return $row;

			}else{
				return false;
			}
		}

		//发送原生语句的方法
		public function query($sql)
		{
			
			//按空格拆分sql语句
			$res = explode(' ',$sql)[0];

			//判断用户发送了什么语句
			switch($res){
				case "insert":

					//发送sql语句
					$bool = mysqli_query($this->link,$sql);

					//判断
					if($bool!=false && mysqli_affected_rows($this->link)>0){

						//返回添加成功数据的id
						return mysqli_insert_id($this->link);
					}else{
						return false;
					}

					break;

				case "delete":

					//发送sql语句
					$bool = mysqli_query($this->link,$sql);

					//判断
					if($bool!=false && mysqli_affected_rows($this->link)>0){

						//返回添加成功数据的id
						return mysqli_affected_rows($this->link);
					}else{
						return false;
					}

					break;

				case "update":

					//发送sql语句
					$bool = mysqli_query($this->link,$sql);

					//判断
					if($bool!=false && mysqli_affected_rows($this->link)>0){

						//返回添加成功数据的id
						return mysqli_affected_rows($this->link);
					}else{
						return false;
					}

					break;

				case "select":

					//发送sql语句
					$result = mysqli_query($this->link,$sql);

					//定义存储所有信息的变量
					$data = array();

					//判断是否查询成功
					if($result!=false && mysqli_num_rows($result)>0){

						//解析结果集
						while($rows = mysqli_fetch_assoc($result)){
							$data[] = $rows;
						}

						//返回结果
						return $data;
					}else{
						return false;
					}

					break;
			}
			var_dump($res);
		}
	}