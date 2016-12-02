<?php
/**
 * 拼车一族模块微站定义
 *
 * @author Yoby
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
 function emojien($str){
    if(!is_string($str))return $str;
    if(!$str || $str=='undefined')return '';

    $text = json_encode($str);
    $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
        return addslashes($str[0]);
    },$text); 
    return json_decode($text);
}
//显示解码
 function emojide($str){
    $text = json_encode($str);
    $text = preg_replace_callback('/\\\\\\\\/i',function($str){
        return '\\';
    },$text);
    return json_decode($text);
}
class Yoby_carModuleSite extends WeModuleSite {

	public function __construct(){//构造函数
		global $_W;
		$this->openid = $_W["openid"];
		$this->yobyurl = $_W['siteroot']."addons/yoby_car/template/mobile/";
		$this->weid = $_W["uniacid"];
 $isreg= pdo_fetchcolumn("select headimgurl from ".tablename('yoby_car_fans')."  where  weid=".$this->weid." and openid='".$this->openid."' ");
if(empty($isreg) && !empty($this->openid)){

        if($_W['account']['level']==3){
         
        load()->model('mc');
        $userinfo = mc_fansinfo($_W['openid']); 
        $userinfo = $userinfo['tag'];
        }else{
      	 $userinfo = mc_oauth_userinfo();
      	 }
      	 
      	 
	$nickname=$userinfo['nickname'];
	 $headimgurl=$userinfo['avatar'];
	$createtime = time();

	pdo_query("insert ignore into ".tablename('yoby_car_fans')."(weid,openid,nickname,headimgurl,createtime) values({$this->weid},'{$this->openid}','$nickname','$headimgurl',$createtime)");
	

}
		
	}
	public function doMobileIndex(){
		global $_W,$_GPC;
		$guanzhu = $this->module['config']['guanzhu'];//关注连接
		$page = $this->module['config']['page'];//分页数量
		$xy = htmlspecialchars_decode($this->module['config']['xy']);
		$weid = $this->weid;
		$id = intval($_GPC['id']);
		if(!$this->is_weixin() ){die('<script type="text/javascript">alert("调皮,怎么在电脑上打开呢!");</script>');}
		if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$this->weid." and openid='".$this->openid."' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
       
              if(empty($this->openid)){
	   	echo "<script>alert('openid不存在,请通过关键词进入');</script>";
	   }
       	if(!empty($id)){
			$item = pdo_fetch("SELECT * FROM ".tablename('yoby_car_fans')." where id=$id");		
				
			}
			
		if(checksubmit('submit')){
		$type =$_GPC['type'];
		$mobile = $_GPC['mobile'];
		$title = $_GPC['title'];
		$sex = $_GPC['sex'];
		$cid = $_GPC['cid'];
		$sid= $_GPC['sid'];
   
//dump($_GPC);
		pdo_update('yoby_car_fans', array('title'=>$title,'phone'=>$mobile,'cid'=>$cid,'sid'=>$sid,'sex'=>$sex,'type'=>$type),array('weid'=>$weid,'openid'=>$this->openid));
		
		die('<script>location.href="'.$_W['siteroot']."app/index.php?i=$weid&c=entry&do=chenke&m=yoby_car".'"</script>');
		
				
	}else{	
	
	include $this->template('fm');
	}		
}
	
	public function doMobileFm(){
		global $_W,$_GPC;
		$guanzhu = $this->module['config']['guanzhu'];//关注连接
		$page = $this->module['config']['page'];//分页数量
		$weid = $this->weid;
		$item = pdo_fetch("SELECT phone FROM ".tablename('yoby_car_fans')." where weid=$weid  and  openid='".$this->openid."' ");
		if(!empty($item['phone'])){
			die('<script>location.href="'.$_W['siteroot']."app/index.php?i=$weid&c=entry&do=chenke&m=yoby_car".'"</script>');
		}else{
			die('<script>location.href="'.$_W['siteroot']."app/index.php?i=$weid&c=entry&do=index&m=yoby_car".'"</script>');
		}
		
		
	}
	public function doMobileajax1() {
	global $_W, $_GPC;
	load()->func('file');
	$weid = $_W['uniacid'];
	$mid = $_GPC['mid'];
	$data = $this->downimg($mid);
	$snid = date('YmdHis') . str_pad(mt_rand(1, 99999),2, '0', STR_PAD_LEFT);
	
	$filename ="images/$weid/".$snid.'.jpg';
file_put_contents(ATTACHMENT_ROOT.'/'.$filename, $data);
 echo '{"src":"'.tomedia($filename).'","v":"'.$filename.'"}';
	}	
	
		
		
	
	public function doMobileChenke(){//乘客行程
		global $_W,$_GPC;
		$guanzhu = $this->module['config']['guanzhu'];//关注连接
		$page = $this->module['config']['page'];//分页数量
		$is_show = $this->module['config']['is_show'];//是否显示过期数据
		$weid = $this->weid;
		$weixin = $_W['account']['name'];
		$share_icon = tomedia($this->module['config']['share_icon']);
		$share_title = $this->module['config']['share_title'];
		$share_desc=  $this->module['config']['share_desc'];
		if(!$this->is_weixin() ){die('<script type="text/javascript">alert("调皮,怎么在电脑上打开呢!");</script>');}
		if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$this->weid." and openid='".$this->openid."' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
       
           if(empty($this->openid)){
	   	echo "<script>alert('openid不存在,请通过关键词进入');</script>";
	   }
       $pindex = max(1, intval($_GPC['page']));
		$psize =$page;
		$condition="";
if($is_show==0){
	$condition.="  and  sendtime>'".date('Y-m-d H:i',time())."'  ";
}
if (!empty($_GPC['keyword'])) {
				$condition .= " AND (A.address1 LIKE '%".$_GPC['keyword']."%'  or  A.address2 LIKE '%".$_GPC['keyword']."%' )";
			}
			$list = pdo_fetchall("SELECT A.id,A.address1,A.address2,A.createtime,A.sendtime,A.num,A.rmb,A.isok,A.beizhu,A.type,B.title,B.sex,B.phone,B.isok,A.openid,B.openid  FROM ".tablename('yoby_car')." as  A ,".tablename('yoby_car_fans')." as B  WHERE  A.type=1 and A.openid=B.openid and  A.weid =$weid $condition  ORDER BY A.id DESC,A.sendtime desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car') ." as  A ,".tablename('yoby_car_fans')." as B  WHERE  A.type=1 and A.openid=B.openid and  A.weid =$weid $condition ");
			$pager = $this->pager($total, $pindex, $psize);
       	
	include $this->template('chenke');
}

	public function doMobileView() {//详情页
	global $_W,$_GPC;
	$guanzhu =$this->module['config']['guanzhu'];$oid =$this->module['config']['oid'];
	$weid = $this->weid;
	$weixin = $_W['account']['name'];
	$id = $_GPC['id'];
	$openid = $this->openid;
	if(!$this->is_weixin() ){die('<script type="text/javascript">alert("调皮,怎么在电脑上打开呢!");</script>');}
		if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$this->weid." and openid='".$this->openid."' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
       
       
	       if(empty($this->openid)){
	   	echo "<script>alert('openid不存在,请通过关键词进入');</script>";
	   }
	$sql = "SELECT * FROM ".tablename('yoby_car')." WHERE id = ".$id;

	$rs = pdo_fetch($sql);
	
	$fans = pdo_fetch('SELECT * FROM ' . tablename('yoby_car_fans') . " WHERE weid =$weid and openid='".$rs['openid']."' ");
	$fans1 = pdo_fetch('SELECT * FROM ' . tablename('yoby_car_fans') . " WHERE weid =$weid and openid='".$this->openid."' ");
	include $this->template('view');	
	
	}
	
	
	public function doMobileSay(){//留言
	global $_W,$_GPC;
	$weid = $_W['uniacid'];
	$id = intval($_GPC['id']);
			$say = trim($_GPC['say']);
			$fopenid = $_W['openid'];
			
			$topenid = $_GPC['topenid'];
			
			if(!empty($say)){
				$data = array(
				'weid'=>$weid,
				'createtime'=>time(),
				'cid'=>$id,
				'content'=>emojien($say),
				'fopenid'=>$fopenid,
				'topenid'=>$topenid,
				
				);
			pdo_insert('yoby_car_say', $data);
			echo json_encode(array('code'=>1));
			
			}
			
}

		public function doMobileDelsay(){//删除一条留言
	global $_W,$_GPC;
	$id = intval($_GPC['id']);
	if(pdo_delete('yoby_car_say', array('id' => $id)))
  {echo json_encode(array('code'=>1));
  }else{
  echo json_encode(array('code'=>0));
  }
}
	public function doMobileJf() {//积分记录
			global $_W,$_GPC;

		$weid = $_W['uniacid'];
		$openid = $_W['openid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize =10;
		$guanzhu = $this->module['config']['guanzhu'];//关注引导链接
		 if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$weid." and openid='$openid' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_log')." WHERE weid =$weid and openid='$openid'  ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_log') . " WHERE weid =$weid and openid='$openid' ");
			$pager = $this->pager($total, $pindex, $psize);
		
		include $this->template('jf');	

	}
	public function doMobileAdd() {//加入车主行程
	global $_GPC,$_W;

	$weid = $this->weid;

	$id = $_GPC['id'];
	$mid =$this->module['config']['mid'];
	$openid = $_GPC['openid'];
	
	$rs = pdo_fetch('SELECT * FROM ' . tablename('yoby_car_add') . " WHERE weid =$weid  and  cid=$id  and openid='".$this->openid."' ");
	if(empty($rs)){
		$data = array(
		'weid'=>$weid,
		'openid'=>$this->openid,
		'cid'=>$id,
		'createtime'=>time(),
		);
		pdo_insert('yoby_car_add',$data);
		pdo_query("UPDATE ".tablename('yoby_car')." SET num=num-1 WHERE id=$id and  num>0 ");
		echo 1;
		//发送模板消息
		if(!empty($mid)){
			 $fans = pdo_fetch('SELECT * FROM ' . tablename('yoby_car_fans') . " WHERE weid =$weid and openid='".$this->openid."' ");
		$snid = date('YmdHis') . str_pad(mt_rand(1, 99999), 3, '0', STR_PAD_LEFT);
		$sex = array(1=>'男',2=>'女');
		$type =array(1=>'乘客',2=>'车主');
		$sex = $sex[$fans['sex']];
		$type = $type[$fans['type']];
		 $arr = array(); 
$dataarr =array(
'新用户'.$fans['title'].'加入您的出行',$snid,$fans['title'].'的电话'.$fans['phone'].',性别是'.$sex.',类型是'.$type,'拼车1人加入您的车队,稍后他可能会联系您,请不要拒接电话.',date('Y-m-d H:i',time()),'点击查看详情'
);
$ss = '{{first.DATA}}
订单号：{{keyword1.DATA}}
订单信息：{{keyword2.DATA}}
订单需求：{{keyword3.DATA}}
时间：{{keyword4.DATA}}
{{remark.DATA}}';
preg_match_all('/{{(.*).DATA}}/',$ss,$rs);
foreach($rs[1] as  $k=>$v){
$arr[$v] = array(
  'value'=>$dataarr[$k]
);
}
$arr['first']['color']='#04be02';
$arr['remark']['color']='#18b4ed';
$this->sendtpl($openid,$mid, $arr,$_W['siteroot']."app/index.php?i=$weid&c=entry&do=view&m=yoby_car&id=$id",'#FF683F');
}
		
	}else{
		echo 0;
	}
	
	
	}
	public function doMobileChezhu(){//车主行程
		global $_W,$_GPC;
		$guanzhu = $this->module['config']['guanzhu'];//关注连接
		$page = $this->module['config']['page'];//分页数量
		$is_show = $this->module['config']['is_show'];
		$weid = $this->weid;
		$weixin = $_W['account']['name'];
		$share_icon = tomedia($this->module['config']['share_icon']);
		$share_title = $this->module['config']['share_title'];
		$share_desc=  $this->module['config']['share_desc'];
		if(!$this->is_weixin() ){die('<script type="text/javascript">alert("调皮,怎么在电脑上打开呢!");</script>');}
		if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$this->weid." and openid='".$this->openid."' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
       
           if(empty($this->openid)){
	   	echo "<script>alert('openid不存在,请通过关键词进入');</script>";
	   }
       $pindex = max(1, intval($_GPC['page']));
		$psize =$page;
		$condition="";
		if($is_show==0){
	$condition.="  and  sendtime>'".date('Y-m-d H:i',time())."'  ";
}
if (!empty($_GPC['keyword'])) {
				$condition .= " AND (A.address1 LIKE '%".$_GPC['keyword']."%'  or  A.address2 LIKE '%".$_GPC['keyword']."%' )";
			}
			$list = pdo_fetchall("SELECT A.id,A.address1,A.address2,A.createtime,A.sendtime,A.num,A.rmb,A.isok,A.beizhu,A.type,B.title,B.sex,B.phone,B.isok,A.openid,B.openid  FROM ".tablename('yoby_car')." as  A ,".tablename('yoby_car_fans')." as B  WHERE A.type=2 and A.openid=B.openid and  A.weid =$weid $condition  ORDER BY A.id DESC,A.sendtime desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car') ." as  A ,".tablename('yoby_car_fans')." as B  WHERE  A.type=2 and A.openid=B.openid and  A.weid =$weid $condition ");
			$pager = $this->pager($total, $pindex, $psize);	
	include $this->template('chezhu');
}
	public function doMobileFabu(){//发布行程
		global $_W,$_GPC;
		$guanzhu = $this->module['config']['guanzhu'];//关注连接
		$shou = $this->module['config']['shou'];//是否手动
		$jifen= floatval($this->module['config']['jifen']);
		//$xy = htmlspecialchars_decode($this->module['config']['xy']);
		
		$mapkey = $this->module['config']['mapkey'];
		
		$weid = $this->weid;
		$id = intval($_GPC['id']);
		$address = $this->module['config']['address'];
		$fans = pdo_fetch('SELECT * FROM ' . tablename('yoby_car_fans') . " WHERE weid =$weid and openid='".$this->openid."' ");
		
		if(!$this->is_weixin() ){die('<script type="text/javascript">alert("调皮,怎么在电脑上打开呢!");</script>');}
	if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$this->weid." and openid='".$this->openid."' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
       
       if(empty($this->openid)){
	   	echo "<script>alert('openid不存在,请通过关键词进入');</script>";
	   }
       	if(!empty($id)){
			$item = pdo_fetch("SELECT * FROM ".tablename('yoby_car')." where id=$id");		
				
			}
			
		if(checksubmit('submit')){
		
		$type =$fans['type'];
		$address1 = $_GPC['address1'];
		$address2 = $_GPC['address2'];
		$sendtime = $_GPC['sendtime'];
		$num = $_GPC['num'];
		$rmb = $_GPC['rmb'];
		$beizhu= $_GPC['beizhu'];
   
 $data = array(
 'weid'=>$weid,
 'type'=>$type,
 'address1'=>$address1,
  'address2'=>$address2,
 'sendtime'=>$sendtime,
 'num'=>$num,
 'rmb'=>$rmb,
 'beizhu'=>$beizhu,
 'openid'=>$this->openid,
 'createtime'=>time(),
 'isok'=>1,
 
 );
 		if(empty($id)){
		pdo_insert('yoby_car', $data);
		$id = pdo_insertid();
		$do = ($type==1)?"chenke":"chezhu";
		
		 load()->model('mc');
		 $today = date('Y-m-d',time());   
		$uid = $_W['member']['uid'];
    $openid=$this->openid;  
     if($jifen>0){
     $row = pdo_fetch("SELECT * FROM " . tablename('yoby_car_log') . " WHERE tid=$id  and   openid='$openid'   and   rectime='$today'   and f=1 ");
     if(empty($row)){
     pdo_insert('yoby_car_log',array('weid'=>$weid,'openid'=>$openid,'createtime'=>time(),'jifen'=>$jifen,'rectime'=>$today,'f'=>1,'tid'=>$id));//分享送积分
    
     mc_credit_update($uid, 'credit1', $jifen, array(0,"发布拼车增加{$jifen}积分"));
     
     }
     }
		
		
		
		die('<script>location.href="'.$_W['siteroot']."app/index.php?i=$weid&c=entry&do=$do&m=yoby_car".'"</script>');	
		}else{
			unset($data['createtime'],$data['weid']);
		pdo_update('yoby_car',$data,array('id'=>$id));
		die('<script>location.href="'.$_W['siteroot']."app/index.php?i=$weid&c=entry&do=gl1&m=yoby_car".'"</script>');
		}
		
		
		
		
				
	}else{	
	
	include $this->template('fabu');
	}
}

	public function doMobileGl(){//管理个人中心
		global $_W,$_GPC;
		$guanzhu = $this->module['config']['guanzhu'];//关注连接
		$page = $this->module['config']['page'];//分页数量
		$weid = $this->weid;
		if(!$this->is_weixin() ){die('<script type="text/javascript">alert("调皮,怎么在电脑上打开呢!");</script>');}
		if(!empty($guanzhu)){//不填写表示不关注
       $follow = pdo_fetchcolumn('SELECT follow FROM ' . tablename('mc_mapping_fans')."  where uniacid=".$this->weid." and openid='".$this->openid."' " );
       if(empty($follow)){
        echo "<script>window.location.href='".$guanzhu."' </script>";
       }
       }
       
             if(empty($this->openid)){
	   	echo "<script>alert('openid不存在,请通过关键词进入');</script>";
	   }
      $fans = pdo_fetch("SELECT * FROM ".tablename('yoby_car_fans')." where weid=$weid  and  openid='".$this->openid."'"); 
       	
	include $this->template('gl');
}

	public function doMobileGl1(){//进行中的行程
	global $_W,$_GPC;
		$page = $this->module['config']['page'];//分页数量
		$weid = $this->weid;
 $pindex = max(1, intval($_GPC['page']));
		$psize =10;
		$condition="  and  openid='".$this->openid."'";
		$time = date('Y-m-d H:i',time());
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car')."  WHERE  weid=$weid  and sendtime>'$time' $condition  ORDER BY sendtime desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car') ."  WHERE weid =$weid and sendtime>'$time' $condition ");
			$pager = $this->pager($total, $pindex, $psize);		
		
		
		include $this->template('gl1');
	}
	public function doMobileDel(){//删除一条进行中出行信息
	global $_W,$_GPC;
	$id = intval($_GPC['id']);
	if(pdo_delete('yoby_car', array('id' => $id)))
  {echo json_encode(array('code'=>1));
  }else{
  echo json_encode(array('code'=>0));
  }
}	

	public function doMobileGl2(){//已完成的行程
	global $_W,$_GPC;
		$page = $this->module['config']['page'];//分页数量
		$weid = $this->weid;
 $pindex = max(1, intval($_GPC['page']));
		$psize =10;
		$condition="  and  openid='".$this->openid."'";
		$time = date('Y-m-d H:i',time());
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car')."  WHERE  weid=$weid  and sendtime<='$time' $condition  ORDER BY sendtime desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car') ."  WHERE weid =$weid and sendtime<='$time' $condition ");
			$pager = $this->pager($total, $pindex, $psize);		
		
		
		include $this->template('gl2');
	}
	public function doMobileDel2(){//删除一条已完成出行信息
	global $_W,$_GPC;
	$id = intval($_GPC['id']);
	if(pdo_delete('yoby_car', array('id' => $id)))
  {echo json_encode(array('code'=>1));
  }else{
  echo json_encode(array('code'=>0));
  }
}

	public function doMobileGl3(){//已加入的行程
	global $_W,$_GPC;
		$page = $this->module['config']['page'];//分页数量
		$weid = $this->weid;
 $pindex = max(1, intval($_GPC['page']));
		$psize =10;
		$condition="  and  openid='".$this->openid."'";
		$time = date('Y-m-d H:i',time());
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_add')."  WHERE  weid=$weid   $condition  ORDER BY createtime desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_add') ."  WHERE weid =$weid  $condition ");
			$pager = $this->pager($total, $pindex, $psize);		
		
		
		include $this->template('gl3');
	}
	public function doMobileDel3(){//删除一条已完成出行信息
	global $_W,$_GPC;
	$id = intval($_GPC['id']);
	if(pdo_delete('yoby_car_add', array('cid' => $id,'openid'=>$this->openid,'weid'=>$this->weid)))
  {echo json_encode(array('code'=>1));
  }else{
  echo json_encode(array('code'=>0));
  }
}

public function pager($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) {
	global $_W;
	$pdata = array(
		'tcount' => 0,
		'tpage' => 0,
		'cindex' => 0,
		'findex' => 0,
		'pindex' => 0,
		'nindex' => 0,
		'lindex' => 0,
		'options' => ''
	);
	if($context['ajaxcallback']) {
		$context['isajax'] = true;
	}

	$pdata['tcount'] = $tcount;
	$pdata['tpage'] = ceil($tcount / $psize);
	if($pdata['tpage'] <= 1) {
		return '';
	}
	$cindex = $pindex;
	$cindex = min($cindex, $pdata['tpage']);
	$cindex = max($cindex, 1);
	$pdata['cindex'] = $cindex;
	$pdata['findex'] = 1;
	$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
	$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
	$pdata['lindex'] = $pdata['tpage'];

	if($context['isajax']) {
		if(!$url) {
			$url = $_W['script_name'] . '?' . http_build_query($_GET);
		}
		$pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
		$pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
		$pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
		$pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
	} else {
		if($url) {
			$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
			$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
			$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
			$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
		} else {
			$_GET['page'] = $pdata['findex'];
			$pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['pindex'];
			$pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['nindex'];
			$pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['lindex'];
			$pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
		}
	}

	$html = '	<div class="p bg-gray" style="height:32px;">
	<div class="pager"  id="pager"><div class="pager-left">';

		$html .= "<div class=\"pager-first\"><a {$pdata['faa']} class=\"pager-nav\">首页</a></div>";
		$html .= "<div class=\"pager-pre\"><a {$pdata['paa']} class=\"pager-nav\">上一页</a></div>";
	$html .='</div><div class="pager-cen">
					' .$pindex.'/'.$pdata['tpage'].'
				</div><div class="pager-right">';

		$html .= "<div class=\"pager-next\"><a {$pdata['naa']} class=\"pager-nav\">下一页</a></div>";
		$html .= "<div class=\"pager-end\"><a {$pdata['laa']} class=\"pager-nav\">尾页</a></div>";
	
	$html .= '</div></div></div>';
	return $html;
}
public function is_weixin() {
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	if (! strpos ( $agent, "icroMessenger" )) {
		return false;
	}
	return true;
}
public function downimg($meid){//下载多媒体
$token = WeAccount::token();
$data = $this->get("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$token&media_id=$meid");


return $data;
}
 public function get($url,$ssl=false){   
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HEADER, 0);
if($ssl){
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$data  =  curl_exec($ch);
curl_close($ch);
return $data; 
}
public function post($url,$msg,$ssl=TRUE){//post ssl
$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL,$url);
if($ssl){
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

}
curl_setopt($ch, CURLOPT_POSTFIELDS,$msg);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

return $data;
    }
    
         public function kefu($openid,$content) {//发送客服消息
 load()->classs('weixin.account');
 $token =WeAccount::token();
 $msg = '{
    "touser":"'.$openid.'",
    "msgtype":"text",
    "text":
    {
         "content":"'.$content.'"
    }
}';
$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$token;
$this->post($url,$msg);
   
}
        public function sendtpl($openid, $tpl_id, $postdata, $url = '', $topcolor = '#FF683F'){
      $token =WeAccount::token();
        $data = array();
        $data['touser'] = $openid;
        $data['template_id'] = trim($tpl_id);
        $data['url'] = trim($url);
        $data['topcolor'] = trim($topcolor);
        $data['data'] = $postdata;
        $data = json_encode($data);
        $post_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$token}";
        $response = $this->post($post_url, $data);
        //return true;
    } 
//时间线,参数是时间戳
public function timeline($time){  
    $t = time()-$time;  
    $f = array(  
        '31536000'=>'年',  
        '2592000'=>'个月',  
        '604800'=>'星期',  
        '86400'=>'天',  
        '3600'=>'小时',  
        '60'=>'分钟',  
        '1'=>'秒'  
    );  
    foreach($f as $k=>$v){  
        if(0 != $c = floor($t/(int)$k)){  
            return $c.$v.'前';  
        }  
    }  
}
/*
后台管理开始------------------------------------------------------------------
*/
public function doWebChezhu() {//车主后台管理
	global $_W,$_GPC;
		load()->func('file');
		load()->func('tpl');
		$weid = $_W['uniacid'];
		
		$sexarr = array(1=>'男','女');
		$typearr = array(1=>'乘客','车主');
		$isokarr = array('未认证','认证');
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
			
				
				$row = pdo_fetchall("SELECT openid FROM ".tablename('yoby_car_fans')." WHERE  id in(".$ids.")");
				
				foreach($row as $v){
					pdo_delete('yoby_car', array('weid' => $weid,'openid'=>$v['openid']));
					pdo_delete('yoby_car_add', array('weid' => $weid,'openid'=>$v['openid']));
				}
			
			$sqls = "delete from  ".tablename('yoby_car_fans')."  where   id in(".$ids.")"; 
			pdo_query($sqls);
			message('删除成功！', referer(), 'success');
			}
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT openid FROM ".tablename('yoby_car_fans')." WHERE  id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('chezhu', array('op' => 'display')), 'error');
			}
		
			
			pdo_delete('yoby_car', array('weid' => $weid,'openid'=>$row['openid']));
			pdo_delete('yoby_car_add', array('weid' => $weid,'openid'=>$row['openid']));
			pdo_delete('yoby_car_fans', array('id' => $id));
			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND (title LIKE '%".$_GPC['keyword']."%'  or  nickname LIKE '%".$_GPC['keyword']."%')  ";
			}		
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_fans') ." WHERE weid=$weid  and type=2 and phone!='' ".$condition."  ORDER BY id desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_fans') ." WHERE weid=$weid and type=2  and phone!=''  ".$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('chezhu');	
		}else if('shenhe'==$op){//认证
			
			$id = intval($_GPC['id']);
			$openid = $_GPC["openid"];
			$isok = intval($_GPC['isok']);
			if($isok==0){
			$this->kefu($openid,"您的注册信息已通过审核!");
			}else{
			$this->kefu($openid,"您违规发布信息已被拉黑或注册信息未通过审核,检查后重试,并联系管理员!");
			
			}
			$issend =($isok==1)?0:1;
			$data = array('isok'=>$issend,);
			pdo_update('yoby_car_fans', $data, array('id' => $id));
			echo json_encode(array('code'=>1));
		}
	
	
	
	}
	
	
public function doWebRz(){

			$data = array('isok'=>1,);
			pdo_update('yoby_car_fans', $data);message('操作成功', referer(), 'success');
}
public function doWebChenke() {//乘客后台管理
	global $_W,$_GPC;
		load()->func('file');
		load()->func('tpl');
		$weid = $_W['uniacid'];
		
		$sexarr = array(1=>'男','女');
		$typearr = array(1=>'乘客','车主');
		$isokarr = array('未认证','认证');
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
			
				
				$row = pdo_fetchall("SELECT openid FROM ".tablename('yoby_car_fans')." WHERE  id in(".$ids.")");
				
				foreach($row as $v){
					pdo_delete('yoby_car', array('weid' => $weid,'openid'=>$v['openid']));
					pdo_delete('yoby_car_add', array('weid' => $weid,'openid'=>$v['openid']));
				}
			
			$sqls = "delete from  ".tablename('yoby_car_fans')."  where   id in(".$ids.")"; 
			pdo_query($sqls);
			message('删除成功！', referer(), 'success');
			}
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT openid FROM ".tablename('yoby_car_fans')." WHERE  id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('chenke', array('op' => 'display')), 'error');
			}
		
			
			pdo_delete('yoby_car', array('weid' => $weid,'openid'=>$row['openid']));
			pdo_delete('yoby_car_add', array('weid' => $weid,'openid'=>$row['openid']));
			pdo_delete('yoby_car_fans', array('id' => $id));
			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND (title LIKE '%".$_GPC['keyword']."%'  or  nickname LIKE '%".$_GPC['keyword']."%')  ";
			}		
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_fans') ." WHERE weid=$weid  and type=1 and phone!='' ".$condition."  ORDER BY id desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_fans') ." WHERE weid=$weid and type=1 and phone!=''  ".$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('chenke');	
		}else if('shenhe'==$op){//认证
			
			$id = intval($_GPC['id']);
			$openid = $_GPC["openid"];
			$isok = intval($_GPC['isok']);
			if($isok==0){
			$this->kefu($openid,"您的注册信息已通过审核!");
			}else{
			$this->kefu($openid,"您违规发布信息已被拉黑或注册信息未通过审核,检查后重试,并联系管理员!");
			
			}
			$issend =($isok==1)?0:1;
			$data = array('isok'=>$issend,);
			pdo_update('yoby_car_fans', $data, array('id' => $id));
			echo json_encode(array('code'=>1));
		}
	
	
	
	}

public function doWebChezhux() {//车主行程后台管理
	global $_W,$_GPC;
		load()->func('file');
		load()->func('tpl');
		$weid = $_W['uniacid'];
		
		$sexarr = array(1=>'男','女');
		$typearr = array(1=>'乘客','车主');
		$isokarr = array('未认证','认证');
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
			
				
				
				foreach($_GPC['delete'] as $v){

					pdo_delete('yoby_car_add', array('weid' => $weid,'cid'=>$v));
				}
			
			$sqls = "delete from  ".tablename('yoby_car')."  where   id in(".$ids.")"; 
			pdo_query($sqls);
			message('删除成功！', referer(), 'success');
			}
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_car')." WHERE  id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('chezhux', array('op' => 'display')), 'error');
			}
					pdo_delete('yoby_car_add', array('weid' => $weid,'cid'=>$id));
			pdo_delete('yoby_car', array('id' => $id));

			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND (address1 LIKE '%".$_GPC['keyword']."%'  or  address2 LIKE '%".$_GPC['keyword']."%')  ";
			}		
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car') ." WHERE weid=$weid  and type=2 ".$condition."  ORDER BY id desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car') ." WHERE weid=$weid and type=2    ".$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('chezhux');	
		}
	
	
	
	}

public function doWebChezhuxlist() {//车主出行加入列表
	global $_W,$_GPC;
		$weid = $_W['uniacid'];
		$id = intval($_GPC['id']);
	$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
			
			$sqls = "delete from  ".tablename('yoby_car_add')."  where   id in(".$ids.")"; 
			pdo_query($sqls);
			message('删除成功！', referer(), 'success');
			}
			$cid = intval($_GPC['cid']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_car_add')." WHERE  id = :id", array(':id' => $cid));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('chezhuxlist', array('op' => 'display')), 'error');
			}
			
			pdo_delete('yoby_car_add', array('id' => $cid));

			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
					
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_add') ." WHERE weid=$weid  and cid=$id ".$condition."  ORDER BY id desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_add') ." WHERE weid=$weid and cid=$id    ".$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('chezhuxlist');	
		}
	}
public function doWebChenkex() {//乘客出行
	global $_W,$_GPC;
		load()->func('file');
		load()->func('tpl');
		$weid = $_W['uniacid'];
		
		$sexarr = array(1=>'男','女');
		$typearr = array(1=>'乘客','车主');
		$isokarr = array('未认证','认证');
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
			
				
				
				foreach($_GPC['delete'] as $v){

					pdo_delete('yoby_car_add', array('weid' => $weid,'cid'=>$v));
				}
			
			$sqls = "delete from  ".tablename('yoby_car')."  where   id in(".$ids.")"; 
			pdo_query($sqls);
			message('删除成功！', referer(), 'success');
			}
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_car')." WHERE  id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('chenkex', array('op' => 'display')), 'error');
			}
					pdo_delete('yoby_car_add', array('weid' => $weid,'cid'=>$id));
			pdo_delete('yoby_car', array('id' => $id));

			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND (address1 LIKE '%".$_GPC['keyword']."%'  or  address2 LIKE '%".$_GPC['keyword']."%')  ";
			}		
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car') ." WHERE weid=$weid  and type=1 ".$condition."  ORDER BY id desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car') ." WHERE weid=$weid and type=1    ".$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('chenkex');	
		}
	
	
	
	}
	
	public function doWebChenkexlist() {//车主出行加入列表
	global $_W,$_GPC;
		$weid = $_W['uniacid'];
		$id = intval($_GPC['id']);
	$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
			
			$sqls = "delete from  ".tablename('yoby_car_add')."  where   id in(".$ids.")"; 
			pdo_query($sqls);
			message('删除成功！', referer(), 'success');
			}
			$cid = intval($_GPC['cid']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_car_add')." WHERE  id = :id", array(':id' => $cid));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('chenkexlist', array('op' => 'display')), 'error');
			}
			
			pdo_delete('yoby_car_add', array('id' => $cid));

			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
					
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_add') ." WHERE weid=$weid  and cid=$id ".$condition."  ORDER BY id desc LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_add') ." WHERE weid=$weid and cid=$id    ".$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('chenkexlist');	
		}
	}
	public function doWebAds() {//广告管理
		global $_W,$_GPC;
	load()->func('file');
	load()->func('tpl');
		
	$weid = $_W['uniacid'];
		
	$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';	
		
	if('post' == $op){
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$item = pdo_fetch("SELECT * FROM ".tablename('yoby_car_ad')." where id=$id");
			empty($item)?message('抱歉数据不存在', '', 'error'):"";	
			}
			if(checksubmit('submit')){
				empty ($_GPC['title'])?message('广告标题不能为空'):$title =$_GPC['title'];
				$img = $_GPC['img'];$url = $_GPC['url'];
				$orderby = $_GPC['orderby'];
				$arr = array('title'=>$title,'img'=>$img,'url'=>$url,'weid'=>$weid,'orderby'=>$orderby);
				
				if(empty($id)){
						pdo_insert('yoby_car_ad',$arr );
						message('数据加成功！', $this->createWebUrl('ads', array('op' => 'display')), 'success');
				}else{
						pdo_update('yoby_car_ad',$arr, array('id' => $id));
						message('数据更新成功！', $this->createWebUrl('ads', array('op' => 'display')), 'success');
				}
				
				
			}else{
				include $this->template('ads');
			}		
	}else if('del' == $op){
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
				
				$row1 = pdo_fetchall("SELECT id,img FROM ".tablename('yoby_car_ad')." WHERE id in(".$ids.")");
				if(!empty($row1)){
					foreach($row1 as $data1){
					if (!empty($data1['img'])) {
			file_delete($data1['img']);
		}	
					}
				}
				$sqls = "delete from  ".tablename('yoby_car_ad')."  where id in(".$ids.")"; 
				pdo_query($sqls);
				message('删除成功！', referer(), 'success');
			}
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_car_ad')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('ads', array('op' => 'display')), 'error');
			}
				if (!empty($row['img'])) {
			file_delete($row['img']);
		}
			pdo_delete('yoby_car_ad', array('id' => $id));
			message('删除成功！', referer(), 'success');
			
		}elseif('display' == $op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%".$_GPC['keyword']."%' ";
			}		
			$list = pdo_fetchall("SELECT *  FROM ".tablename('yoby_car_ad') ." WHERE weid =". $weid.$condition."  ORDER BY orderby DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_car_ad') ." WHERE weid =". $weid.$condition);
			$pager = pagination($total, $pindex, $psize);
	include $this->template('ads');	
		}else if('shenhe'==$op){
			$id = intval($_GPC['id']);
		
		$issend =($_GPC['isok']==1)?1:0;
			$data1 = array('isok'=>$issend,);
		pdo_update('yoby_car_ad', $data1, array('id' => $id));
		echo json_encode(array('status'=>true));

		}	
		
	}
}
