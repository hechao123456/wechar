<?php 	
//git 备份操作
//入口文件
require './wx_sample.php';
//实例化
$weixin = new Weixin();
//验证方法
//
if ($_GET['echostr']) {

	$weixin->valid();
}else{
//调用消息管理方法
	$wechat->responseMsg();
}



 ?>