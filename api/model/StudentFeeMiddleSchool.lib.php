<?php
/*
* Infrastructure: Chives React Admin
* Author: Chives Network
* Email: reactchives@gmail.com
* Copyright (c) 2023
* License: GPL V3 or Commercial license
*/

Class StudentFeeMiddleSchool
{
    private $db;
	private $debug;
	function __construct($debug=0) {
       global $db;
       $this->db = $db;
	   $this->debug=$debug;
    }
	
	public function 学期进一位($学期,$位数)
	{
		$入学学期ARRAY 	= explode('-',$学期);
		$处理年份 		= floor($位数/2);
		$处理上下学期	= $位数%2;
		$入学学期ARRAY[0]      = (INT)$入学学期ARRAY[0];
		$入学学期ARRAY[1]      = (INT)$入学学期ARRAY[1];
		if($处理年份>0)								{
			$入学学期ARRAY[0] += $处理年份;
			$入学学期ARRAY[1] += $处理年份;
		}
		if($处理上下学期==1)					{
			switch($入学学期ARRAY[2])		{
				case '第一学期':
					$入学学期ARRAY[2] = "第二学期";
					break;
				case '第二学期':
					$入学学期ARRAY[0] += 1;
					$入学学期ARRAY[1] += 1;
					$入学学期ARRAY[2]  = "第一学期";
					break;
			}
		}
		return join("-",$入学学期ARRAY);;
	}
	
	public function 是否学部最后一个学期($当前年级,$当前学期)
	{
		$当前学期ARRAY 	= explode('-',$当前学期);
		if($当前学期ARRAY[2]=="第一学期")			{
			return "否";
		}
		$是否学部最后一个学期 = "否";
		switch($当前年级)					{
			case '小学六年级':
				$是否学部最后一个学期 = "是";
				break;
			case '初中九年级':
				$是否学部最后一个学期 = "是";
				break;
			case '高中高三级':
				$是否学部最后一个学期 = "是";
				break;
			case '高复文科班':
				$是否学部最后一个学期 = "是";
				break;
			case '高复理科班':
				$是否学部最后一个学期 = "是";
				break;
		}
		return $是否学部最后一个学期;
	}
	
	public function 学生应缴费学期($stuinfo)
	{
		global $全局变量_班级表;
		$班级		= $stuinfo['班级'];
		$年级		= $stuinfo['年级'];
		$学部		= $stuinfo['学部'];
		$入学学期	= $stuinfo['入学学期'];
		$结果						= array();		
		for($i=0;$i<12;$i++)			{
			$当前学期X 					= $this->学期进一位($入学学期,$i);
			$是否学部最后一个学期		= $this->是否学部最后一个学期($年级,$当前学期X);
			$结果[$当前学期X] 			= $是否学部最后一个学期;
			if($是否学部最后一个学期=="是" || substr($当前学期X,0,4)>date('Y') || ( substr($当前学期X,0,4)==date('Y') && strpos($当前学期X,"第二学期")>0 ) )		{
				return $结果;
			}
		}
		//print_R($入学学期);exit;
		return $结果;
	}
	
	public function 得到所有学期的收费标准()
	{
		global $全局变量_班级表;
		$sql 	= "select * from data_middle_shoufeibiaozhun";
		$rs_a	= $this->db->CacheGetAll(180,$sql);
		$得到所有学期的收费标准=array();
		foreach($rs_a as $item)			{
			$收费标准	= $item['收费标准'];
			$得到所有学期的收费标准[$收费标准] 	= $item;
		}
		return $得到所有学期的收费标准;
	}
	
	public function 得到支付方式()
	{
		$SQLADD = " and DictMark='收费单_支付方式' and IsEnable='1'";
		$sql 	= "select * from form_formdict where 1=1 $SQLADD order by SortNumber asc";
		$rs		= $this->db->CacheExecute(180,$sql);
		$rs_a 	= $rs->GetArray();
		$RS		= array();
		foreach($rs_a AS $Element)			{
			$RS[] = $Element['ChineseName'];
		}
		return $RS;
	}
	
	public function 学生缴费标准($stuinfo)
	{
		global $全局变量_班级表,$得到所有学期的收费标准;
		$得到所有学期的收费标准 = $this->得到所有学期的收费标准();
		$班级		= $stuinfo['班级'];
		$年级		= $stuinfo['年级'];
		$学部		= $stuinfo['学部'];
		$收费标准	= $stuinfo['收费标准'];
		$入学学期	= $stuinfo['入学学期'];
		$学费折扣	= $stuinfo['学费折扣'];
		$住宿费折扣	= $stuinfo['住宿费折扣'];
		$伙食费折扣	= $stuinfo['伙食费折扣'];
		$床上用品校服费折扣	= $stuinfo['床上用品校服费折扣'];
		$代管费折扣	= $stuinfo['代管费折扣'];
		
		$sql 		= "select 学期名称 from data_xueqi";
		$rs 		= $this->db->CacheExecute(180,$sql);
		$rs_a 		= $rs->GetArray();
		$学期名称ARRAY = array();
		foreach($rs_a AS $ROW)		{
			$学期名称ARRAY[]	= $ROW['学期名称'];
		}
		
		$学生应缴费学期 	= $this->学生应缴费学期($stuinfo);
		//print_R($得到所有学期的收费标准);print_R($学生应缴费学期);exit;
		$学生缴费标准		= array();
		if(is_array($学生应缴费学期))		{
			foreach($学生应缴费学期 AS $学期名称 => $是否学部最后一个学期)			{
				if(in_array($学期名称,$学期名称ARRAY)&&$得到所有学期的收费标准[$收费标准]['学费']!="")			{
					
					//更新自定义指定学期的学费折扣
					if($学费折扣=="全校标准")			{
						$学费折扣	= $得到所有学期的收费标准[$收费标准]['学费折扣'];
					}
					else if($学费折扣=="免费")			{
						$学费折扣	= 0;
					}
					if($住宿费折扣=="全校标准")			{
						$住宿费折扣	= 1; //$得到所有学期的收费标准[$收费标准]['住宿费折扣'];
					}
					else if($住宿费折扣=="免费")			{
						$住宿费折扣	= 0;
					}
					if($伙食费折扣=="全校标准")			{
						$伙食费折扣	= 1; //$得到所有学期的收费标准[$收费标准]['伙食费折扣'];
					}
					else if($伙食费折扣=="免费")			{
						$伙食费折扣	= 0;
					}
					if($床上用品校服费折扣=="全校标准")			{
						$床上用品校服费折扣	= 1; //$得到所有学期的收费标准[$收费标准]['床上用品校服费折扣'];
					}
					else if($床上用品校服费折扣=="免费")			{
						$床上用品校服费折扣	= 0;
					}
					if($代管费折扣=="全校标准")			{
						$代管费折扣	= 1; //$得到所有学期的收费标准[$收费标准]['代管费折扣'];
					}
					else if($代管费折扣=="免费")			{
						$代管费折扣	= 0;
					}
					//只记录最近一个学期的数据.
					$学生缴费标准 = array();
					$学生缴费标准[$学期名称]['学费'] 				= $得到所有学期的收费标准[$收费标准]['学费']*$学费折扣;
					$学生缴费标准[$学期名称]['住宿费'] 				= $得到所有学期的收费标准[$收费标准]['住宿费']*$住宿费折扣;
					$学生缴费标准[$学期名称]['伙食费'] 				= $得到所有学期的收费标准[$收费标准]['伙食费']*$伙食费折扣;
					$学生缴费标准[$学期名称]['床上用品校服费']		 = $得到所有学期的收费标准[$收费标准]['床上用品校服费']*$床上用品校服费折扣;
					$学生缴费标准[$学期名称]['代管费'] 				= $得到所有学期的收费标准[$收费标准]['代管费']*$代管费折扣;
					//$学生缴费标准[$学期名称]['学费折扣'] 			= $学费折扣;
				}
			}
		}
		
		//print_R($stuinfo);
		//print_R("学费折扣:".$学费折扣);
		//print_R($得到所有学期的收费标准);
		//print_R($得到所有学期的收费标准);
		//print_R($学生缴费标准);
		return $学生缴费标准;
	}
	
	public function 某个学生的应缴费信息($stuinfo)
	{
		global $全局变量_班级表;
		global $得到所有学期的收费标准;
		$班级		= $stuinfo['班级'];
		$入学学期	= $stuinfo['入学学期'];
		$学部 		= $stuinfo['学部'];
		$年级 		= $stuinfo['年级'];
		$收费标准	= $stuinfo['收费标准'];
		$学费折扣	= $stuinfo['学费折扣'];
		$住宿费折扣	= $stuinfo['住宿费折扣'];
		$伙食费折扣	= $stuinfo['伙食费折扣'];
		$学生缴费标准 	= $this->学生缴费标准($stuinfo);
		$_SESSION['学生缴费标准']				= $学生缴费标准;
				
		$某个学生的应缴费信息	= array();
		if(is_array($学生缴费标准))		{
			foreach($学生缴费标准 AS $学期名称 => $缴费标准)			{
				$某个学生某学期的已缴费信息	= $this->某个学生某学期的已缴费信息($学期名称,$stuinfo);
				$_SESSION['已缴费信息'][$学期名称] 	= $某个学生某学期的已缴费信息;
				$_SESSION['stuinfo']				= $stuinfo;
				//print_R($缴费标准);
				//标准缴费
				$COUNTER = 0;
				foreach($缴费标准 AS $缴费项目 => $缴费金额)			{
					$某个学生的应缴费信息[$学期名称][$缴费项目]['编号']		= $COUNTER;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['名称']		= $缴费项目;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴']		= number_format($缴费金额, 2, '.', '');;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['缴费金额'], 2, '.', '');
					$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['缴费金额'], 2, '.', '');
					$某个学生的应缴费信息[$学期名称][$缴费项目]['退费']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['退费金额'], 2, '.', '');
					$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费']		= number_format($缴费金额-$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']-$某个学生的应缴费信息[$学期名称][$缴费项目]['退费'], 2, '.', '');
					$缴费项目标准列表[$缴费项目] = $缴费项目;
					$COUNTER ++;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['退费'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['退费']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['计划缴费']		= $某个学生的应缴费信息[$学期名称][$缴费项目]['欠费'];
					$某个学生的应缴费信息[$学期名称][$缴费项目]['已结清文本']	= "已结清";
					$某个学生的应缴费信息[$学期名称][$缴费项目]['只读']			= "是";
					
					$某个学生的应缴费信息[$学期名称][$缴费项目]['是否启用']				= $得到所有学期的收费标准[$收费标准]["启用".$缴费项目];
					$某个学生的应缴费信息[$学期名称][$缴费项目]['未启用时文本描述']		= "未开始缴费";
					
					if($学费折扣=="未设置" || $学费折扣=="全校标准")			{
						$学费折扣 = $得到所有学期的收费标准[$收费标准]["学费折扣"];
					}
					if($学费折扣=="免学费" || $学费折扣=="" || $学费折扣=="0")			{
						$某个学生的应缴费信息[$学期名称]['学费']['学费折扣']				= " (免学费)";
					}
					elseif($学费折扣==1)		{
						$某个学生的应缴费信息[$学期名称]['学费']['学费折扣']				= " (全额)";
					}
					else	{
						$某个学生的应缴费信息[$学期名称]['学费']['学费折扣']				= " (".($学费折扣*10)."折)";
					}
					
					if($住宿费折扣=="全免" || $住宿费折扣=="" || $住宿费折扣=="0")			{
						$某个学生的应缴费信息[$学期名称]['住宿费']['住宿费折扣']				= " (全免)";
					}
					else if($住宿费折扣=="全校标准")			{
						$某个学生的应缴费信息[$学期名称]['伙食费']['住宿费折扣']				= "";
					}
					else	{
						$某个学生的应缴费信息[$学期名称]['住宿费']['住宿费折扣']				= " (".($住宿费折扣*10)."折)";
					}
					
					if($伙食费折扣=="全免" || $伙食费折扣=="" || $伙食费折扣=="0")			{
						$某个学生的应缴费信息[$学期名称]['伙食费']['伙食费折扣']				= " (全免)";
					}
					else if($伙食费折扣=="全校标准")			{
						$某个学生的应缴费信息[$学期名称]['伙食费']['伙食费折扣']				= "";
					}
					else	{
						$某个学生的应缴费信息[$学期名称]['伙食费']['伙食费折扣']				= " (".($伙食费折扣*10)."折)";
					}
					//print_R($某个学生的应缴费信息);

				}
				//自由缴费,不需要计算欠费金额.
				$缴费项目标准列表 	= array_keys($缴费项目标准列表);
				foreach($某个学生某学期的已缴费信息 AS $缴费项目 => $缴费信息)				{
					if(!in_array($缴费项目,$缴费项目标准列表))					{
						$某个学生的应缴费信息[$学期名称][$缴费项目]['编号']		= $COUNTER;
						$某个学生的应缴费信息[$学期名称][$缴费项目]['名称']		= $缴费项目;
						$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴']		= number_format($缴费信息['缴费金额'], 2, '.', '');
						$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['缴费金额'], 2, '.', '');
						$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['缴费金额'], 2, '.', '');
						$某个学生的应缴费信息[$学期名称][$缴费项目]['退费']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['退费金额'], 2, '.', '');
						$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费']		= 0;
						$COUNTER ++;
						$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴']);
						$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']);
						$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴']);
						$某个学生的应缴费信息[$学期名称][$缴费项目]['退费'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['退费']);
						$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费']);
						$某个学生的应缴费信息[$学期名称][$缴费项目]['计划缴费']		= $某个学生的应缴费信息[$学期名称][$缴费项目]['欠费'];
						$某个学生的应缴费信息[$学期名称][$缴费项目]['已结清文本']	= "已结清";
						$某个学生的应缴费信息[$学期名称][$缴费项目]['只读']			= "是";
					}
				}
			}
		}
		//print_R($某个学生的应缴费信息);
		return $某个学生的应缴费信息;
	}
	
	
	
	public function 某个学生的可选项目缴费信息($stuinfo)
	{
		global $全局变量_班级表;
		global $得到所有学期的收费标准;
		$班级		= $stuinfo['班级'];
		$学部 		= $stuinfo['学部'];
		$年级 		= $stuinfo['年级'];
		$收费标准	= $stuinfo['收费标准'];
		$学期名称	= $stuinfo['入学学期'];
		//$得到所有学期的收费标准 = $this->得到所有学期的收费标准();
		
		//print_R($得到所有学期的收费标准);
		if($得到所有学期的收费标准[$收费标准]['启用夏令营']=="是")																	{
			if($stuinfo['学生状态']=="正常状态" || $stuinfo['学生状态']=="录取成功")			{
				$学生缴费标准[$学期名称]['夏令营'] 				= $得到所有学期的收费标准[$收费标准]['夏令营校内生收费'];
			}
			else	{			
				$学生缴费标准[$学期名称]['夏令营'] 				= $得到所有学期的收费标准[$收费标准]['夏令营校外生收费'];
			}
		}

		$某个学生的应缴费信息	= array();
		if(is_array($学生缴费标准))		{
			foreach($学生缴费标准 AS $学期名称 => $缴费标准)			{
				$某个学生某学期的已缴费信息	= $this->某个学生某学期的已缴费信息($学期名称,$stuinfo);
				//$学生缴费标准 = RSA_GBK_TO_UTF8($学生缴费标准,"gbk","utf-8");print_R($学生缴费标准);
				//标准缴费
				$COUNTER = 0;
				foreach($缴费标准 AS $缴费项目 => $缴费金额)			{
					$某个学生的应缴费信息[$学期名称][$缴费项目]['编号']		= $COUNTER;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['名称']		= $缴费项目;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴']		= number_format($缴费金额, 2, '.', '');;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['缴费金额'], 2, '.', '');
					$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['缴费金额'], 2, '.', '');
					$某个学生的应缴费信息[$学期名称][$缴费项目]['退费']		= number_format($某个学生某学期的已缴费信息[$缴费项目]['退费金额'], 2, '.', '');
					$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费']		= number_format($缴费金额-$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']-$某个学生的应缴费信息[$学期名称][$缴费项目]['退费'], 2, '.', '');
					$缴费项目标准列表[$缴费项目] = $缴费项目;
					$COUNTER ++;
					$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['应缴']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['实缴']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['已缴']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['退费'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['退费']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费'] 	= str_replace(".00","",$某个学生的应缴费信息[$学期名称][$缴费项目]['欠费']);
					$某个学生的应缴费信息[$学期名称][$缴费项目]['计划缴费']		= $某个学生的应缴费信息[$学期名称][$缴费项目]['欠费'];
					$某个学生的应缴费信息[$学期名称][$缴费项目]['已结清文本']	= "已结清";
					$某个学生的应缴费信息[$学期名称][$缴费项目]['只读']			= "是";
					
					$某个学生的应缴费信息[$学期名称][$缴费项目]['是否启用']				= $得到所有学期的收费标准[$收费标准]["启用".$缴费项目];
					$某个学生的应缴费信息[$学期名称][$缴费项目]['未启用时文本描述']		= "未开始缴费";
				
				}
			}
		}
		//print_R($某个学生的应缴费信息);
		return $某个学生的应缴费信息;
	}
	
	public function 某个学生某学期的已缴费信息($学期名称,$stuinfo)
	{
		$sql	= "select 缴费项目,sum(if(缴费金额>0,缴费金额,0)) as 缴费金额,sum(if(缴费金额<0,缴费金额,0)) as 退费金额 from data_middle_newstudent_shoufeimingxi a where 身份证件号='".$stuinfo['身份证件号']."' and 学期='$学期名称' and 是否作废='否' and 缴费状态='缴费成功' group by 缴费项目 order by 缴费金额 desc";
		$rs_c	= $this->db->CacheGetAll(180,$sql);
		$NEWARRAY = array();
		for($i=0;$i<sizeof($rs_c);$i++)		{
			$缴费项目 							= $rs_c[$i]['缴费项目'];
			$NEWARRAY[$缴费项目]['缴费金额'] 	= $rs_c[$i]['缴费金额'];
			$NEWARRAY[$缴费项目]['退费金额'] 	= $rs_c[$i]['退费金额'];
		}
		$_SESSION['某个学生某学期的已缴费信息_SQL'] = $sql;
		return $NEWARRAY;
	}
	
	public function 某个学生所有缴费单($stuinfo)
	{
		$sql	= "select * from data_middle_newstudent_shoufeidan where 身份证件号='".$stuinfo['身份证件号']."' and 是否作废='否' and 缴费状态='缴费成功' order by 缴费时间 desc";
		$rs_c	= $this->db->CacheGetAll(180,$sql);
		return $rs_c;
	}
	
	public function 生成新的缴费单号()
	{
		$one			= 1;
		$trade_no		= '';
		while($one!='')
		{
			$trade_no	= "DDKJ-PC-".date("Ymd")."-".date("His")."-".rand(1000,9999);			
			$sql		= "select 编号 from data_middle_newstudent_shoufeidan where 订单编号=?";
			$one		= $this->db->GetOne($sql,array($trade_no));
		}
		return $trade_no;
	}
	
	public function 输出调试($debug)					{
		global $全局变量_班级表;
		$this->debug=$debug;
	}
	
	public function 重新生成缴费总账()					{
		global $全局变量_班级表;
		$sql 		= "select distinct 下学期就读年级 from data_middle_newstudent where 学部=''";
		$rs 		= $this->db->Execute($sql);
		$rs_a 		= $rs-> GetArray();
		for($i=0;$i<sizeof($rs_a);$i++)				{
			$下学期就读年级	= $rs_a[$i]['下学期就读年级'];
			$学部			= returntablefield("data_middle_nianji","名称",$下学期就读年级,"学部");
			$sql			= "update data_middle_newstudent set 学部='$学部' where 下学期就读年级='$下学期就读年级'";
			//print $sql."<BR>";
			$this->db->Execute($sql);
		}
		//exit;
			
		//老生缴费同步
		$sql		= "select * from data_newstudent where 1=1";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		//$rs_a		= array();
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		$数据库表字段	= array();
		$insertSqlArray	= array();
		foreach ($rs_a as $stuinfo)			{		
			$某个学生的应缴费信息 	= $this->某个学生的应缴费信息($stuinfo);
			$更新字段 				= array();
			$退费合计				= 0;
			$欠费合计				= 0;
			$实缴合计				= 0;
			$某个学生的应缴费信息KEYS 	= array_keys($某个学生的应缴费信息);
			//所计算的是当前学期
			$缴费学期					= array_pop($某个学生的应缴费信息KEYS);
			//所计算的是下个学期
			//$缴费学期					= array_pop($某个学生的应缴费信息KEYS);
			$缴费项目信息				= $某个学生的应缴费信息[$缴费学期];
			//if($stuinfo['学部']=="初中部")		{
				//print_R($stuinfo);print_R($某个学生的应缴费信息);print_R($某个学生的应缴费信息KEYS);print_R($缴费学期);print_R($缴费项目信息);exit;
			//}
			$更新字段[]				= " 缴费学期 = '".$缴费学期."'";
			foreach($缴费项目信息 AS $缴费项目 => $缴费项目值)				{				
				$fields				= array();
				$fields['学期']		= $缴费学期;
				$fields['收费标准']	= $stuinfo['收费标准'];
	
				$fields['缴费项目']	= $缴费项目;
				$fields['应缴金额']	= $缴费项目值['应缴'];
				$fields['实缴金额']	= $缴费项目值['实缴'];
				$fields['退费金额']	= $缴费项目值['退费'];
				$fields['欠费金额']	= $缴费项目值['欠费'];
				$fields['计算时间']	= date('Y-m-d H:i:s');
				//$insertSqlArray[]	= "('".join("','",array_values($fields))."')";
				//$this->insertInToTableByArray('edu_shoufei2_main1',$fields,$this->db);
				$退费合计			+= $缴费项目值['退费'];
				$欠费合计			+= $缴费项目值['欠费'];
				$实缴合计			+= $缴费项目值['实缴'];
				$更新字段[]			= " $缴费项目 = '".$缴费项目值['实缴']."' , ".$缴费项目."欠费 = '".$缴费项目值['欠费']."' ";
				
			}
			
			$更新字段SQL  = join(',',$更新字段);
			$更新字段SQL .= " , 缴费金额 = '$实缴合计' ";
			$更新字段SQL .= " , 欠费金额 = '$欠费合计' ";
			$更新字段SQL .= " , 退费金额 = '$退费合计' ";
			if($实缴合计>0)		{
				$更新字段SQL .= " , 缴费状态 = '已缴费' ";
			}
			else	{
				$更新字段SQL .= " , 缴费状态 = '' ";
			}
			$sql = "update data_newstudent set 
					$更新字段SQL
					where 编号='".$stuinfo['编号']."'
					";
			$this->db->Execute($sql);
			//print_R($某个学生的应缴费信息);
			//print_R($sql);exit;
			//print_R($stuinfo);print_R($某个学生的应缴费信息);print_R($insertSqlArray);exit;
		}
		//$keysarr		= array_keys($fields);
		//$数据库表字段	= join(',',$keysarr);
		//if(!empty($insertSqlArray))				{
		//	$count+=$Utility->批量插入数据表('data_middle_newstudent_shoufei_zongzhang',$数据库表字段,$insertSqlArray,100);
		//	if($this->debug) print_R($insertSqlArray)."<BR>";
		//}
		//exit;
		
		//新生缴费同步
		$sql		= "select * from data_middle where 录取状态='录取成功'";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		//print_R($rs_a);;
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		$数据库表字段	= array();
		$insertSqlArray	= array();
		foreach ($rs_a as $stuinfo)			{		
		
			$stuinfo['入学学期'] 		= $stuinfo['计划入学学期'];;
			$stuinfo['学号'] 			= $stuinfo['身份证件号'];
			$stuinfo['姓名'] 			= $stuinfo['学生姓名'];		
			$stuinfo['学生状态']		= $stuinfo['录取状态'];		
			$stuinfo['班级']			= "新生";
			$下学期就读年级				= $stuinfo['下学期就读年级'];
			$stuinfo['学部'] 			= returntablefield("data_middle_nianji","名称",$下学期就读年级,"学部");
			$stuinfo['年级'] 			= $下学期就读年级;	
			
			$stuinfo['收费标准']		= $stuinfo['入学学期']."-".$stuinfo['学部'];
			$某个学生的应缴费信息 		= $this->某个学生的应缴费信息($stuinfo);
			$更新字段 				= array();
			$退费合计				= 0;
			$欠费合计				= 0;
			$实缴合计				= 0;
			$某个学生的应缴费信息KEYS 	= array_keys($某个学生的应缴费信息);
			$缴费学期					= array_pop($某个学生的应缴费信息KEYS);
			$缴费项目信息				= $某个学生的应缴费信息[$缴费学期];
			
			$更新字段[]				= " 缴费学期 = '".$缴费学期."'";
			foreach($缴费项目信息 AS $缴费项目 => $缴费项目值)				{				
				$fields				= array();
				$fields['学期']		= $缴费学期;
				$fields['收费标准']	= $stuinfo['收费标准'];
	
				$fields['缴费项目']	= $缴费项目;
				$fields['应缴金额']	= $缴费项目值['应缴'];
				$fields['实缴金额']	= $缴费项目值['实缴'];
				$fields['退费金额']	= $缴费项目值['退费'];
				$fields['欠费金额']	= $缴费项目值['欠费'];
				$fields['计算时间']	= date('Y-m-d H:i:s');
				//$insertSqlArray[]	= "('".join("','",array_values($fields))."')";
				//$this->insertInToTableByArray('edu_shoufei2_main1',$fields,$this->db);
				$退费合计			+= $缴费项目值['退费'];
				$欠费合计			+= $缴费项目值['欠费'];
				$实缴合计			+= $缴费项目值['实缴'];
				$更新字段[]			= " $缴费项目 = '".$缴费项目值['实缴']."' , ".$缴费项目."欠费 = '".$缴费项目值['欠费']."' ";
				
			}
			
			$更新字段SQL  = join(',',$更新字段);
			$更新字段SQL .= " , 缴费金额 = '$实缴合计' ";
			$更新字段SQL .= " , 欠费金额 = '$欠费合计' ";
			$更新字段SQL .= " , 退费金额 = '$退费合计' ";
			if($实缴合计>0)		{
				$更新字段SQL .= " , 缴费状态 = '已缴费' ";
			}
			else	{
				$更新字段SQL .= " , 缴费状态 = '' ";
			}
			$sql = "update data_middle set 
					$更新字段SQL
					where 编号='".$stuinfo['编号']."'
					";
			$this->db->Execute($sql);
			//print_R($sql);print "<BR>\n";
			//print_R($某个学生的应缴费信息);
			//print_R($stuinfo);print_R($某个学生的应缴费信息);print_R($insertSqlArray);exit;
		}
		//$keysarr		= array_keys($fields);
		//$数据库表字段	= join(',',$keysarr);
		//if(!empty($insertSqlArray))				{
		//	$count+=$Utility->批量插入数据表('data_middle_newstudent_shoufei_zongzhang',$数据库表字段,$insertSqlArray,100);
		//	if($this->debug) print_R($insertSqlArray)."<BR>";
		//}
		
	}
	
	public function insertInToTableByArray($tablename,$fieldsarray,$db)
	{
		$keys=array_keys($fieldsarray);
		$values=array_values($fieldsarray);
		$sql="insert into $tablename (".join(',',$keys).") values ('".join("','",$values)."')";
		$db->Execute($sql);
	}
	
	public function 学生信息($stucode)			{
		global $db,$_POST,$全局变量_班级表;		
		$sql					= "select * from data_student where 学号='$stucode' or 身份证件号='$stucode' ";
		$stuinfo				= $db->GetRow($sql);
		$stuinfo['显示信息']	= "身份证:".substr($stuinfo['身份证件号'],0,6)."***".substr($stuinfo['身份证件号'],-4)." ".$stuinfo['年级'];
		if($stuinfo['班级']=="" || $stuinfo['班级']=="新生")			{
			$sql					= "select * from data_newstudent where 身份证件号='$stucode' ";
			$stuinfo				= $db->GetRow($sql);
			$stuinfo['入学学期'] 	= $stuinfo['计划入学学期'];
			$stuinfo['学号'] 		= $stuinfo['身份证件号'];
			$stuinfo['姓名'] 		= $stuinfo['学生姓名'];		
			$stuinfo['学生状态']	= $stuinfo['录取状态'];		
			$stuinfo['班级']		= "新生";	
			$stuinfo['是否新生']	= "是";
			$下学期就读年级			= $stuinfo['下学期就读年级'];
			$stuinfo['学部'] 		= returntablefield("data_middle_nianji","年级",$下学期就读年级,"学部")['学部'];
			$stuinfo['年级'] 		= $下学期就读年级;
			$stuinfo['收费标准'] 	= $stuinfo['入学学期']."-".$stuinfo['学部'];
			$stuinfo['显示信息']	= "身份证:".substr($stuinfo['身份证件号'],0,6)."***".substr($stuinfo['身份证件号'],-4)." ".$stuinfo['年级'];
		}
		elseif($stuinfo['学号']!=""&&$stuinfo['班级']!="")	{
			//老生
			$stuinfo['是否新生']	= "否";
		}
		return $stuinfo;
	}
	
	public function 微信小程序学生应缴费接口输出($stucode)
	{
		global $db,$_POST,$全局变量_班级表;		
		$stuinfo = $this->学生信息($stucode);

		$RSA 							= array();
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['姓名'] 		= $stuinfo['姓名'];
		$RSA['学生信息']['班级'] 		= $stuinfo['班号'];
		$RSA['学生信息']['学部'] 		= $stuinfo['学部'];
		$RSA['学生信息']['年级'] 		= $stuinfo['年级'];
		$RSA['学生信息']['入学学期'] 	= $stuinfo['入学学期'];
		$RSA['学生信息']['身份证件号'] 	= $stuinfo['身份证件号'];
		$RSA['学生信息']['性别'] 		= $stuinfo['性别'];
		$RSA['学生信息']['收费标准'] 	= $stuinfo['收费标准'];
		$RSA['学生信息']['学生状态'] 	= $stuinfo['学生状态'];
		$RSA['学生信息']['显示信息'] 	= $stuinfo['显示信息'];

		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];

		$某个学生的应缴费信息	= $this->某个学生的应缴费信息($stuinfo);
		$学期列表				= array_keys($某个学生的应缴费信息);
		$学期应缴费				= array();
		$COUNTER				= 0;
		//print_R($某个学生的应缴费信息);
		foreach($某个学生的应缴费信息 AS $学期名称 => $应缴费数组)			{
			$学期应缴合计 				= 0;
			$学期欠费合计 				= 0;
			foreach($应缴费数组 AS $应缴费数组KEY)				{
				$学期应缴合计 += $应缴费数组KEY['应缴'];
				if($应缴费数组KEY['是否启用']=="是")				{
					$学期欠费合计 += $应缴费数组KEY['欠费'];
				}
			}
			$Element 					= array();
			$Element['编号']			= $COUNTER;
			$Element['学期名称']		= $学期名称;
			$Element['学期应缴合计']	= $学期应缴合计;
			$Element['学期欠费合计']	= $学期欠费合计;
			$Element['缴费提示标题']	= "重要提示";
			$Element['缴费提示内容']	= "缴费前请确认以下信息是否准确:学生姓名:".$stuinfo['姓名']." 学生身份证件号:".$stuinfo['身份证件号']." 缴费金额:".$学期欠费合计."?";
			$Element['每个学期应缴费']	= array_values($应缴费数组);
			$学期应缴费[$COUNTER] 		= $Element;
			$COUNTER++;
		}

		$RSA['学期列表']	= $学期列表;
		$RSA['学期应缴费']	= $学期应缴费;
		$RSA['_SESSION']	= $_SESSION;
		$RSA['_REQUEST']	= $_REQUEST;

		print json_encode($RSA);
	}
	
	
	public function 微信小程序夏令营等学生可选项目应缴费接口($stucode)
	{
		global $db,$_POST,$全局变量_班级表;		
		$stuinfo = $this->学生信息($stucode);

		$RSA 							= array();
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['姓名'] 		= $stuinfo['姓名'];
		$RSA['学生信息']['班级'] 		= $stuinfo['班号'];
		$RSA['学生信息']['学部'] 		= $stuinfo['学部'];
		$RSA['学生信息']['年级'] 		= $stuinfo['年级'];
		$RSA['学生信息']['入学学期'] 	= $stuinfo['入学学期'];
		$RSA['学生信息']['身份证件号'] 	= $stuinfo['身份证件号'];
		$RSA['学生信息']['性别'] 		= $stuinfo['性别'];
		$RSA['学生信息']['学生状态'] 	= $stuinfo['学生状态'];
		$RSA['学生信息']['显示信息'] 	= $stuinfo['显示信息'];

		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];

		$某个学生的可选项目缴费信息	= $this->某个学生的可选项目缴费信息($stuinfo);
		$学期列表				= array_keys($某个学生的可选项目缴费信息);
		$学期应缴费				= array();
		$COUNTER				= 0;
		//$某个学生的可选项目缴费信息 = RSA_GBK_TO_UTF8($某个学生的可选项目缴费信息,"gbk","utf-8");print_R($某个学生的可选项目缴费信息);
		foreach($某个学生的可选项目缴费信息 AS $学期名称 => $应缴费数组)			{
			$学期应缴合计 				= 0;
			$学期欠费合计 				= 0;
			foreach($应缴费数组 AS $应缴费数组KEY)				{
				$学期应缴合计 += $应缴费数组KEY['应缴'];
				if($应缴费数组KEY['是否启用']=="是")				{
					$学期欠费合计 += $应缴费数组KEY['欠费'];
				}
			}
			$Element 					= array();
			$Element['编号']			= $COUNTER;
			$Element['学期名称']		= $学期名称;
			$Element['学期应缴合计']	= $学期应缴合计;
			$Element['学期欠费合计']	= $学期欠费合计;
			$Element['缴费提示标题']	= "重要提示";
			$Element['缴费提示内容']	= "缴费前请确认以下信息是否准确:学生姓名:".$stuinfo['姓名']." 学生身份证件号:".$stuinfo['身份证件号']." 缴费金额:";
			$Element['每个学期应缴费']	= array_values($应缴费数组);
			$学期应缴费[$COUNTER] 		= $Element;
			$COUNTER++;
		}

		$RSA['学期列表']	= $学期列表;
		$RSA['学期应缴费']	= $学期应缴费;

		return $RSA;
	}
	
	public function 微信小程序_校园商城_应缴费接口($stucode)
	{
		global $db,$_POST,$全局变量_班级表;		
		$stuinfo = $this->学生信息($stucode);

		$RSA 							= array();
		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		$RSA['学生信息']['姓名'] 		= $stuinfo['姓名'];
		$RSA['学生信息']['班级'] 		= $stuinfo['班号'];
		$RSA['学生信息']['学部'] 		= $stuinfo['学部'];
		$RSA['学生信息']['年级'] 		= $stuinfo['年级'];
		$RSA['学生信息']['入学学期'] 	= $stuinfo['入学学期'];
		$RSA['学生信息']['身份证件号'] 	= $stuinfo['身份证件号'];
		$RSA['学生信息']['性别'] 		= $stuinfo['性别'];
		$RSA['学生信息']['学生状态'] 	= $stuinfo['学生状态'];
		$RSA['学生信息']['显示信息'] 	= $stuinfo['显示信息'];

		$RSA['学生信息']['学号'] 		= $stuinfo['学号'];
		
		$学期列表				= array_keys($某个学生的可选项目缴费信息);
		$学期应缴费				= array();
		$COUNTER				= 0;
		$JiaoFeiID  			= $_POST['JiaoFeiID'];
		$JiaoFeiTotalAmount  	= intval($_POST['JiaoFeiTotalAmount']);
		$sql 		= "select * from data_middle_store where 编号='$JiaoFeiID'";
		$rs 		= $db->Execute($sql);
		$rs_a 		= $rs->GetArray();	
		$编号 		= $rs_a[0]['编号'];
		$名称 		= $rs_a[0]['名称'];
		$一级分类 	= $rs_a[0]['一级分类'];
		$二级分类 	= $rs_a[0]['二级分类'];
		$类型 		= $rs_a[0]['类型'];
		if($JiaoFeiTotalAmount>0)		{
			$价格 		= $JiaoFeiTotalAmount;
		}
		else	{
			$价格 		= $rs_a[0]['价格'];
		}
		
		$_REQUEST['支付金额'] 	= $价格;
		$_REQUEST['支付描述'] 	= "于".date("Y-m-d H:i:s")."日支付费用:".$_REQUEST['支付金额']." 购买:$名称";
		
		$缴费项目	= $名称;
		$缴费金额	= $价格;
		$应缴费数组[$缴费项目]['编号']		= $COUNTER;
		$应缴费数组[$缴费项目]['名称']		= $缴费项目;
		$应缴费数组[$缴费项目]['应缴']		= number_format($缴费金额, 2, '.', '');;
		$应缴费数组[$缴费项目]['实缴']		= 0;
		$应缴费数组[$缴费项目]['已缴']		= 0;
		$应缴费数组[$缴费项目]['退费']		= 0;
		$应缴费数组[$缴费项目]['欠费']		= $缴费金额;
		
		global $当前学期;
		$Element 					= array();
		$Element['编号']			= $COUNTER;
		$Element['学期名称']		= $当前学期;
		$Element['学期应缴合计']	= $价格;
		$Element['学期欠费合计']	= $价格;
		$Element['缴费提示标题']	= "重要提示";
		$Element['缴费提示内容']	= "缴费前请确认以下信息是否准确:购买商品:".$名称." 价格:".$价格;
		$Element['每个学期应缴费']	= array_values($应缴费数组);
		$学期应缴费[$COUNTER] 		= $Element;
		$COUNTER++;

		$RSA['学期列表']	= $学期列表;
		$RSA['学期应缴费']	= $学期应缴费;

		return $RSA;
	}
	
	public function 微信小程序夏令营等学生可选项目应缴费接口输出($stucode)
	{
		$RSA = $this->微信小程序夏令营等学生可选项目应缴费接口($stucode);
		print json_encode($RSA);
	}
	
	public function STUDENT_JIAOFEI_UTF8CODE_DRAFT($缴费状态='缴费成功',$数据来源='手工缴费')	{
		return $this->学生缴费保存到数据库($缴费状态,$数据来源);
	}
	
	public function 学生缴费保存到数据库($缴费状态='缴费成功',$数据来源='手工缴费')
	{
		global $_POST,$全局变量_班级表;
		$ado		= new Adodb('mysqli');
		$db			= $ado->创建默认数据库连接();
	
		$xueqi		= $_POST['xueqi'];
		$stuid		= $_POST['stuid'];
		$stucode	= $_POST['stucode'];
		$trade_no	= $_POST['trade_no'];
		
		
		$stuinfo = $this->学生信息($stucode);
	
		try			{
			if(empty($xueqi))			{
				$fields['ERROR'] 	= '学期信息为空';
				$fields['stuinfo']	= $stuinfo;
				$fields['_POST']	= $_POST;
				$fields['sql']		= $sql;
				return $fields; 
			}
			if(empty($trade_no))			{
				$fields['ERROR'] 	= '缴费单号不能为空';
				$fields['stuinfo']	= $stuinfo;
				$fields['_POST']	= $_POST;
				$fields['sql']		= $sql;
				return $fields; 
			}
			if(empty($stuinfo))			{
				$fields['ERROR'] 	= '没有查到此学生';
				$fields['stuinfo']	= $stuinfo;
				$fields['_POST']	= $_POST;
				$fields['sql']		= $sql;
				return $fields; 
			}
			$sql	="select 编号 from data_middle_shoufeidan where 订单编号=?";
			if($db->GetOne($sql,$trade_no)!='')		{
				throw new Exception('缴费单号:'.$trade_no.' 已存在,不能重复缴费');
			}
			
			if($_POST['JiaoFeiType']=="CampusStoreWuPin")				{
				//校园商城-开始
				
				$微信小程序_校园商城_应缴费接口	= $this->微信小程序_校园商城_应缴费接口($stucode);
				$缴费学期				= $_POST['xueqi'];
				$应缴费数组				= $微信小程序_校园商城_应缴费接口[$缴费学期];
				//print_R($微信小程序_校园商城_应缴费接口);exit;			
				if($_POST['JiaoFeiID']=="" || $_POST['JiaoFeiItemName']=="" || $_POST['JiaoFeiItemAmount']=="")				{
					$fields['stuinfo']	= $stuinfo;
					$fields['_POST']	= $_POST;
					$fields['ERROR'] 	= '没有正确获取到该学生所要购买的商品信息.';
					return $fields; 
				}
				$fields				= array();
				$fields['学期']		= $缴费学期;
				$fields['学号']		= $stuinfo['学号'];
				$fields['姓名']		= $stuinfo['姓名'];
				$fields['班级']		= $stuinfo['班级'];
				$fields['学部']		= $stuinfo['学部'];
				$fields['年级']		= $stuinfo['年级'];
				$fields['手机']		= $stuinfo['手机'];
				$fields['身份证件号']	= $stuinfo['身份证件号'];
				$fields['学生宿舍']	= $stuinfo['学生宿舍'];
				$fields['床位号']	= $stuinfo['床位号'];
				$fields['是否新生']	= $stuinfo['是否新生'];
				$fields['数据来源']	= $数据来源;
				$fields['订单编号']	= $trade_no;
				$fields['收费人']	= $_SESSION['LOGIN_USER_NAME'];
				//$fields['交易返回']	= '无';
				$fields['支付时间']	= date('Y-m-d H:i:s');
				$fields['名称']		= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiItemName']));
				$fields['金额']		= floatval($_POST['JiaoFeiItemAmount']) * intval(base64_decode($_POST['JiaoFeiNumber']));
				$fields['规格']		= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiModel']));
				$fields['颜色']		= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiColor']));
				$fields['尺码']		= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiSize']));
				$fields['性别']		= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiSex']));
				$fields['数量']		= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiNumber']));
				$fields['一级分类']	= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiGradeOne']));
				$fields['二级分类']	= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiGradeTwo']));
				$fields['支付方式']	= $_POST['paytype'];
				$fields['是否作废']	= '否';
				$fields['OPENID']	= $_POST['openid'];
				$fields['支付状态']	= $缴费状态;
				$keys	= array_keys($fields);
				$values	= array_values($fields);
				$sql	= "insert into data_middle_campusshop (".join(',',$keys).") values ('".join("','",$values)."')";
				$db->Execute($sql);
				if($db->Affected_Rows()!=1)									{
					throw new Exception('错误:插入缴费单失败');
					return ;
				}
				$fields['ERROR'] = "";
				return $fields;	
				exit;
				//校园商城-结束
			}
			elseif($_POST['JiaoFeiType']=="CampusStore")				{
				//校园商城-开始
				$微信小程序夏令营等学生可选项目应缴费接口	= $this->微信小程序夏令营等学生可选项目应缴费接口($stucode);
				$缴费学期				= $_POST['xueqi'];
				$应缴费数组				= $微信小程序夏令营等学生可选项目应缴费接口[$缴费学期];
				//print_R($微信小程序夏令营等学生可选项目应缴费接口);exit;			
				if($_POST['JiaoFeiItemName']=="")				{
					$fields['stuinfo']	= $stuinfo;
					$fields['_POST']	= $_POST;
					$fields['ERROR'] 	= '没有正确获取到该学生的应缴费信息';
					return $fields; 
				}
				$attach					= array();
				$allmoney				= floatval($_POST['JiaoFeiTotalAmount']);
				$newitem				= array();
				$newitem['缴费项目']	= iconv("utf-8","gbk",base64_decode($_POST['JiaoFeiItemName']));
				$newitem['缴费金额']	= floatval($_POST['JiaoFeiTotalAmount']);
				$attach[]				= $newitem;
				//校园商城-结束
			}
			else			{
				//学费-开始
				$某个学生的应缴费信息	= $this->某个学生的应缴费信息($stuinfo);
				$缴费学期				= $_POST['xueqi'];
				$应缴费数组				= $某个学生的应缴费信息[$缴费学期];
				//print_R($某个学生的应缴费信息);exit;			
				if(empty($应缴费数组))				{
					$fields['stuinfo']	= $stuinfo;
					$fields['_POST']	= $_POST;
					$fields['ERROR'] 	= '没有正确获取到该学生的应缴费信息';
					return $fields; 
				}
				$allmoney	= 0;
				$attach		= array();
				foreach($应缴费数组 as $缴费项目=>$item)			{
					$key		= 'input_'.$缴费学期.'_'.$缴费项目;
					//if(!isset($_POST[$key]) || !is_numeric($_POST[$key]))
					//throw new Exception("缴费代码 $缴费项目 的数据未提交或格式不正确");
					$缴费金额	= floatval($_POST[$key]);
					if($缴费金额>0 && $缴费金额>$item['欠费'])
					{
						throw new Exception("缴费代码 $缴费项目 本次缴费金额不能大于".$item['欠费']);
					}
					if($缴费金额<0 && abs($缴费金额)>$item['实缴'])
						throw new Exception("缴费代码 $缴费项目 本次退费金额不能大于".$item['实缴']);	
					$allmoney+=$缴费金额;
					if($缴费金额!=0)			{
						$newitem				= array();
						$newitem['缴费项目']	= $缴费项目;
						$newitem['缴费金额']	= $缴费金额;
						$attach[]				= $newitem;
					}
				}
				//print_R($应缴费数组);			
				$keys = array_keys($应缴费数组);
				foreach($_POST as $key=>$value)							{
					$tmparr=explode('_',$key);
					if(sizeof($tmparr)==3 && $tmparr[0]=='input' && !in_array($tmparr[2],$keys) && floatval($value)!=0)		{
						$allmoney				+= floatval($value);
						$newitem				= array();
						$newitem['缴费项目']	= $tmparr[2];
						$newitem['缴费金额']	= floatval($value);
						$attach[]				= $newitem;
					}
				}
				//学费-结束
			}
			if(sizeof($attach)==0)									{
				throw new Exception("缴费明细至少要有一个");
			}
			
			$db->StartTrans(); 
			$fields				= array();
			$fields['学期']		= $缴费学期;
			$fields['学号']		= $stuinfo['学号'];
			$fields['姓名']		= $stuinfo['姓名'];
			$fields['班级']		= $stuinfo['班级'];
			$fields['学部']		= $stuinfo['学部'];
			$fields['年级']		= $stuinfo['年级'];
			$fields['手机']		= $stuinfo['手机'];
			$fields['身份证件号']	= $stuinfo['身份证件号'];
			$fields['数据来源']	= $数据来源;
			$fields['缴费金额']	= $allmoney;
			$fields['订单编号']	= $trade_no;
			$fields['收费人']	= $_SESSION['LOGIN_USER_NAME'];
			//$fields['交易返回']	= '无';
			$fields['缴费时间']	= date('Y-m-d H:i:s');
			$fields['包含子项']	= serialize($attach);
			$fields['支付方式']	= $_POST['paytype'];
			$fields['是否作废']	= '否';
			$fields['OPENID']	= $_POST['openid'];
			$fields['缴费状态']	= $缴费状态;
			$keys	= array_keys($fields);
			$values	= array_values($fields);
			$sql	= "insert into data_middle_shoufeidan (".join(',',$keys).") values ('".join("','",$values)."')";
			$db->Execute($sql);
			if($db->Affected_Rows()!=1)									{
				throw new Exception('错误:插入缴费单失败');
				return ;
			}
			foreach($attach as $item)									{
				$缴费项目		= $item['缴费项目'];
				$缴费金额		= $item['缴费金额'];
				if($缴费金额!=0)			{
					$xmfields=array();
					$xmfields['学期']		= $缴费学期;
					$xmfields['学号']		= $stuinfo['学号'];
					$xmfields['姓名']		= $stuinfo['姓名'];
					$xmfields['缴费项目']	= $缴费项目;
					$xmfields['缴费金额']	= $缴费金额;
					$xmfields['身份证件号']	= $stuinfo['身份证件号'];
					$xmfields['班级']		= $stuinfo['班级'];
					$xmfields['学部']		= $stuinfo['学部'];
					$xmfields['年级']		= $stuinfo['年级'];
					if($缴费金额>0)
						$xmfields['缴费类型']	= '收费';
					else
						$xmfields['缴费类型']	= '退费';
					$xmfields['是否作废']	= '否';
					$xmfields['缴费状态']	= $缴费状态;
					$xmfields['订单编号']	= $trade_no;
					$xmfields['收费人']		= $_SESSION['LOGIN_USER_NAME'];
					$xmfields['缴费时间']	= date('Y-m-d H:i:s');
					$keys					= array_keys($xmfields);
					$values					= array_values($xmfields);
					$sql	= "insert into data_middle_shoufeimingxi (".join(',',$keys).") value ('".join("','",$values)."')";
					//print $sql."<BR>";
					$db->Execute($sql);
					if($db->Affected_Rows()!=1)									{
						throw new Exception('错误:插入缴费单失败');
						return ;
					}
				}
			}
			$db->CompleteTrans();
			$fields['ERROR'] = "";
			return $fields;			
		} 
		catch(Exception $e)							{
			$fields['ERROR'] = $e->getMessage();
			return $fields; 
		}
	}
	
	public function STUDENT_JIAOFEI_UTF8CODE_PAYMONEY($订单编号,$tradeStatus="SUCCESS")			{
		if($tradeStatus=="SUCCESS")		{
			$缴费状态 = "缴费成功";
		}
		else	{
			$缴费状态 = $tradeStatus;
		}
		$sql	= "update data_middle_newstudent_shoufeimingxi set 缴费状态='$缴费状态' where 订单编号='$订单编号'";
		$rs		= $this->db->Execute($sql);
		$sql	= "update data_middle_newstudent_shoufeidan set 缴费状态='$缴费状态' where 订单编号='$订单编号'";
		$rs		= $this->db->Execute($sql);
		return 1;
	}
	
	public function STUDENT_JIAOFEI_UTF8CODE_PAYMONEY_CAMPUSSHOP($订单编号,$tradeStatus="SUCCESS")			{
		if($tradeStatus=="SUCCESS")		{
			$支付状态 = "缴费成功";
		}
		else	{
			$支付状态 = $tradeStatus;
		}
		$sql	= "update data_middle_newstudent_campusshop set 支付状态='$支付状态' where 订单编号='$订单编号'";
		$rs		= $this->db->Execute($sql);
		//库存需要减1
		$sql	= "select 名称 from data_middle_newstudent_campusshop where 订单编号='$订单编号'";
		$rs		= $this->db->Execute($sql);
		$名称	= $rs->fields['名称'];
		$sql	= "update data_middle_newstudent_store set 库存=库存-1 where 名称='$名称'";
		$rs		= $this->db->Execute($sql);
		return 1;
	}
	
	public function 预缴费数据转换成已缴费($订单编号)			{
		return $this->STUDENT_JIAOFEI_UTF8CODE_PAYMONEY($订单编号);
	}
	

	public function 更新收费单面的手机号()			{
		$sql		= "select * from data_middle where 录取状态='录取成功'";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		foreach ($rs_a as $stuinfo)			{	
			$stuinfo['手机']		= $stuinfo['联系方式1']." ".$stuinfo['联系方式2']." ".$stuinfo['联系电话'];	
			$sql = "update data_middle_newstudent_shoufeidan set 手机='".$stuinfo['手机']."' where 身份证件号='".$stuinfo['身份证件号']."' and 手机=''"; 
			if($this->debug) print $sql."<BR>";
			$this->db->Execute($sql);
		}
	}
	
	public function 更新收费单面的老生手机号()			{
		$sql		= "select * from data_middle_shoufeidan where 手机='' and 班级!='新生'";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		foreach ($rs_a as $stuinfo)			{	
			$家长电话	= returntablefield("data_newstudent","身份证件号",$stuinfo['身份证件号'],"家长电话");
			$sql 		= "update data_middle_newstudent_shoufeidan set 手机='".$家长电话."' where 身份证件号='".$stuinfo['身份证件号']."'"; 
			if($this->debug) print $sql."<BR>";
			$this->db->Execute($sql);
		}
	}
	
	public function 更新收费明细表的身份证件号()			{
		$sql		= "select 订单编号 from data_middle_shoufeimingxi where 身份证件号=''";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		foreach ($rs_a as $Element)			{	
			$订单编号		= $Element['订单编号'];
			$sql 			= "select * from data_middle_shoufeidan where 订单编号='$订单编号'";
			$同步信息 		= $this->db->CacheGetAll(180,$sql);;
			$同步信息		= $同步信息[0];
			if($同步信息['身份证件号']!="")			{
				$sql = "update data_middle_newstudent_shoufeimingxi set 身份证件号='".$同步信息['身份证件号']."',班级='".$同步信息['班级']."',学部='".$同步信息['学部']."',年级='".$同步信息['年级']."' where 订单编号='".$订单编号."'"; 
				if($this->debug) print $sql."<BR>";
				$this->db->Execute($sql);
			}
		}
	}
	
	public function 缴费数据同步到新生数据表的缴费字段()			{
		return '';exit;
		
		$sql		= "select * from data_middle where 录取状态='录取成功'";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		foreach ($rs_a as $stuinfo)							{	
			//print_R($stuinfo);		
			$身份证件号	= $stuinfo['身份证件号'];
			$入学学期	= $stuinfo['计划入学学期'];
			$sql 		= "select * from data_middle_shoufei_zongzhang where 学期='$入学学期' and 班级='新生' and 身份证件号='$身份证件号'";
			$rsX_a		= $this->db->CacheGetAll(180,$sql);
			//print_R($sql);print_R($rsX_a);
			$缴费记录MAP = array();
			$缴费金额	 = 0;
			foreach ($rsX_a as $缴费记录)			{
				$缴费记录MAP[(STRING)$缴费记录['缴费项目']] = $缴费记录['实缴金额'];
				$缴费金额 += $缴费记录['实缴金额'];
			}
			if($缴费金额>0)					{
				//print_R($缴费记录MAP);
				$sql = "update data_middle set 
							缴费状态 = '已缴费' , 
							缴费金额 = '$缴费金额' , 
							学费 = '".$缴费记录MAP['学费']."' ,
							住宿费 = '".$缴费记录MAP['住宿费']."' ,
							伙食费 = '".$缴费记录MAP['伙食费']."' ,
							床上用品校服费 = '".$缴费记录MAP['床上用品校服费']."' ,
							代管费 = '".$缴费记录MAP['代管费']."'
						where 身份证件号='$身份证件号' and 计划入学学期='".$stuinfo['计划入学学期']."'";
				$this->db->Execute($sql);
				//print_R($sql);print "\n";
			}
		}
	}
	
	public function 缴费数据同步到老生数据表的缴费字段()			{
		return '';exit;
		
		global $当前学期;
		//得到最近一个学期		
		$sql		= "select * from data_xueqi order by 流水号 desc";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		$指定学期 	= $rs_a[0]['学期名称'];
		
		//先缓存缴费数据
		$先缓存缴费数据 = array();
		$sql 		= "select * from data_middle_shoufei_zongzhang where 学期='$指定学期' and 班级!='新生'";
		$rsX_a		= $this->db->CacheGetAll(180,$sql);
		foreach ($rsX_a as $stuinfo)							{	
			//print_R($stuinfo);		
			$身份证件号						= $stuinfo['身份证件号'];
			$先缓存缴费数据[$身份证件号][]	= $stuinfo;
		}
		//print_R($先缓存缴费数据);	exit;
		
		$sql		= "select * from data_newstudent where 学生状态='正常状态'";
		$rs_a		= $this->db->CacheGetAll(180,$sql);
		if($this->debug) print $sql."<BR>";
		$count		= 0;
		foreach ($rs_a as $stuinfo)							{	
			//print_R($stuinfo);		
			$编号		= $stuinfo['编号'];
			$身份证件号	= $stuinfo['身份证件号'];
			//$sql 		= "select * from data_middle_shoufei_zongzhang where 学期='$指定学期' and 身份证件号='$身份证件号'";
			//$rsX_a		= $this->db->CacheGetAll(180,$sql);
			//print_R($sql);print_R($rsX_a);
			$缴费记录MAP = array();
			$缴费金额	 = 0;
			foreach ($先缓存缴费数据[$身份证件号] as $缴费记录)			{
				$缴费记录MAP[(STRING)$缴费记录['缴费项目']] = $缴费记录['实缴金额'];
				$缴费金额 += $缴费记录['实缴金额'];
			}
			if($缴费金额>0)					{
				//print_R($缴费记录MAP);
				$sql = "update data_newstudent set 
							缴费状态 = '已缴费' , 
							缴费金额 = '$缴费金额' , 
							学费 = '".$缴费记录MAP['学费']."' ,
							住宿费 = '".$缴费记录MAP['住宿费']."' ,
							伙食费 = '".$缴费记录MAP['伙食费']."' ,
							床上用品校服费 = '".$缴费记录MAP['床上用品校服费']."' ,
							代管费 = '".$缴费记录MAP['代管费']."'
						where 编号='$编号'";
				$this->db->Execute($sql);
				//print_R($sql);print "\n";
			}
		}
	}
	
	//校园商城_接口输出
	public function 校园商城_接口输出($stucode)
	{
		global $db,$_POST,$全局变量_班级表;		
		
		$stuinfo = $this->学生信息($stucode);

		if(empty($stuinfo))				{
			//之前不能有任何输出.
			$RSA			= array();
			$RSA['status'] 	= "ERROR";
			$RSA['msg'] 	= "没有查到此学生";
			print json_encode($RSA);
			exit;
		}
		if(empty($stuinfo['学号']))		{
			//之前不能有任何输出.
			$RSA				= array();
			$RSA['status'] 		= "ERROR";
			$RSA['msg'] 		= "没有正确获取到学号或学生的身份证件号.";
			$RSA['stucode'] 	= $stucode;
			$RSA['_POST'] 		= $_POST;
			$RSA['stuinfo'] 	= $stuinfo;
			print json_encode($RSA);
			exit;
		}
		
		require_once("../../Enginee/newai_icampus.php");
		$sql 		 = "select * from data_middle_store where 是否启用='是' order by 排序号";
		$rsX_a		 = $this->db->CacheGetAll(180,$sql);
		//print_R($sql);print_R($rsX_a);
		$校园商城	 = array();
		foreach ($rsX_a as $校园商城RECORD)			{
			$一级分类 	= $校园商城RECORD['一级分类'];
			$二级分类 	= $校园商城RECORD['二级分类'];
			$名称 		= $校园商城RECORD['名称'];
			$校园商城RECORD['全部图片'] = 获取OA附件的JSON格式数组($校园商城RECORD['图片'],"图片");
			$校园商城RECORD['缩略图片'] = $校园商城RECORD['全部图片'][0];
			
			
			$校园商城['一级分类'][$一级分类] 			= $一级分类;
			$校园商城['二级分类'][$一级分类][$二级分类] = $二级分类;
			$校园商城['所有子项'][$一级分类][] 			= $校园商城RECORD;
		}
		$校园商城['一级分类'] 			= array_keys($校园商城['一级分类']);
		$校园商城['学生信息'] 			= $stuinfo;
		
		print json_encode($校园商城);
	}
	
	
}


?>