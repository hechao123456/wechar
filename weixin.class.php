<?php
//引入配置文件
require './weixin.conf.php';
//定义一个wechat
//所有关于wechat方法
class Weixin{
  //封装
  // private
  private $token;
  //构造方法  初始化参数
  public function __construct(){
    //定义属性
    $this->token = TOKEN;
    $this->textTpl = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Content><![CDATA[%s]]></Content>
          <FuncFlag>0</FuncFlag>
          </xml>";
  }
  //校验方法
  public function valid()
  {
          $echoStr = $_GET["echostr"];
          //valid signature , option
          if ($this->checkSignature()) {
              echo $echoStr;
              exit;
          }
  }
  //消息管理
  public function responseMsg()
  {
          //get post data, May be due to the different environments
          $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
          // file_put_contents('data.xml', $postStr);
          //extract post data
          if (!empty($postStr)) {
              /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                the best way is to check the validity of xml by yourself */
              libxml_disable_entity_loader(true);
              $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
              //对于不同的消息类型
              //进行不同方法的处理
              switch ($postObj->MsgType) {
                case 'text':
                  $this->doText($postObj);
                  break;
                case 'image':
                  $this->doImage($postObj);
                  break;
                case 'voice':
                  $this->doVoice($postObj);
                  break;
                case 'location':
                  $this->doLocation($postObj);
                  break;
                default:
                  break;
              }
            }
  }
  //检查签名
  private function checkSignature()
  {
          // you must define TOKEN by yourself
          if (!defined("TOKEN")) {
              throw new Exception('TOKEN is not defined!');
          }
          $signature = $_GET["signature"];
          $timestamp = $_GET["timestamp"];
          $nonce = $_GET["nonce"];
          $token = $this->token;
          $tmpArr = array($token, $timestamp, $nonce);
          // use SORT_STRING rule
          sort($tmpArr, SORT_STRING);
          $tmpStr = implode($tmpArr);
          $tmpStr = sha1($tmpStr);
          if ($tmpStr == $signature) {
              return true;
          } else {
              return false;
          }
  }
  //文本消息处理方法
  private function doText($postObj)
  {
        $keyword = trim($postObj->Content);
        //xml模板
        if (!empty($keyword)) {
            // $contentStr = "Welcome to wechat world!";
            $contentStr = "你好!我是php59的微信公众号";
            //请求机器人接口
            $url = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg ='.$keyword;
            $contentStr = str_replace("{br}","\r",json_decode(file_get_contents($url))->content);
            //sprintf 拼接模板
            $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
            // file_put_contents('./data1.xml',$resultStr);
            echo $resultStr;
        }
  }
  //图片消息处理方法
  private function doImage($postObj)
  {
    //拼接返回数据模板
    //返回接收到图片url地址
    $resultStr = sprintf($this->textTpl,$postObj->FromUserName, $this->ToUserName,time(),'text',$postObj->PicUrl);
    // file_put_contents('./data1.xml',$resultStr);
    echo $resultStr;
  }
  //语音消息处理方法
  private function doVoice($postObj)
  {
    $MediaID = $postObj->MediaId;
    $resultStr = sprintf($this->textTpl,$postObj->FromUserName, $this->ToUserName,time(),'text','语音接收到,MediaID:'.$MediaID);
    echo $resultStr;
  }
  //地理位置消息处理方法
  private function doLocation($postObj)
  {
    $contentStr = '您所在位置在:纬度为:'.$postObj->Location_X.' 经度为:'.$postObj->Location_Y;
    $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $this->ToUserName, time(), 'text', $contentStr);
    echo $resultStr;
  }
}