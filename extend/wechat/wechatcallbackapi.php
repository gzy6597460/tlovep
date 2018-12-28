<?php
/*
    方倍工作室 
    http://www.cnblogs.com/txw1958/
    CopyRight 2014 All Rights Reserved
*/
namespace wechat;

use \think\Db;
use \app\api\controller\Welogin;
use \think\Cache;
use think\Log;

define("TOKEN", "xlq123123");

$wechatObj = new wechatcallbackapi();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
} else {
    $wechatObj->valid();
}

class wechatcallbackapi
{
    var $appid = "wx5cb120cb7d1c9866";
    var $appsecret = "1f26b36c2792e2cb61dd8c00419b7a08";

    //构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if ($appid) {
            $this->appid = $appid;
        }
        if ($appsecret) {
            $this->appsecret = $appsecret;
        }
        $this->access_token = $this->getAccessToken();
    }

    //验证消息
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    //检查签名
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    //响应消息
    public function responseMsg()
    {
        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents('php://input');
        Log::record('公众号接收消息：' . var_export($postStr, true), 'info');
        if (!empty($postStr)) {
            $this->logger("R " . $postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch ($RX_TYPE) {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: " . $RX_TYPE;
                    break;
            }
            $this->logger("T " . $result);
            echo $result;
        } else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        Log::record('微信事件：' . var_export($object, true), 'info');
        $content = "";
        switch ($object->Event) {
            case "subscribe":
                //$extend = $object->EventKey;
                $extend = explode("_", $object->EventKey);
                $content = "欢迎你关注“小猪圈”，我们以“轻松生活，快乐猪圈”为理念，以“降低使用与迭代成本 ，解决租赁信用体系问题”为思路，为企业和个人提供低值易耗品、分时、免押租赁服务，并提供产品维护、迭代与管理服务 ；从而提升社会资源的利用效率；你只需为使用付费，实现小投入高效率，为你打造轻松生活生态圈。\n 回复“客服”了解免租活动详情！";
                Log::record('扫码参数二维码关注公众号:' . var_export($extend, true), 'info');
                //$content .= (!empty($object->EventKey))?("\n openid:".$object->FromUserName."\n来自二维码场景 ".$extend):"";
                $UserInfo = $this->get_user_info($object->FromUserName);
                $weixin_id = db('member')->where('weixin_id', $object->FromUserName)->find();
                Log::record('用户信息:' . var_export($weixin_id, true), 'info');
                if (empty($weixin_id)) {
                    $data = [
                        'name' => $UserInfo['nickname'],
                        'sex' => $UserInfo['sex'],
                        'weixin_id' => $UserInfo['openid'],
                        'headimgurl' => $UserInfo['headimgurl'],
                        'create_time' => date("Y-m-d H:i:s"),
                        //'referee' => $extend[2]
                    ];
                    $member_id = db('member')->insertGetId($data);
                    db('member_account')->insert(['member_id' => $member_id, 'addr_id' => 0]);
                    if ($extend) {
                        switch ($extend[1]) {
                            case 1://用户分享二维码
                                $referee_id = $extend[2];
                                \db('member')->where('id', $member_id)->update(['referee' => $referee_id]);
                                $referee_count = \db('score_history')->where('member_id', $data['referee'])->where('channel', '推荐新用户奖励')->count();
                                Log::record('老用户___' . var_export($data['referee'], true) . '推荐次数' . var_export($referee_count, true), 'info');
                                if ($referee_count < 8) {
                                    add_score($data['referee'], 100, '推荐新用户奖励');
                                }
                                //add_score($data['referee'], 100, '转发分享送分',$member_id);
                                break;
                            case 2://代理商推广二维码
                                $re_agent_id = $extend[2];
                                $find_agent = \db('agent')->where('id', $re_agent_id)->find();
                                $agent_level = $find_agent['level'];
                                Log::record('代理商二维码推广——推广ID' . var_export($re_agent_id, true) . '代理商存在：' . var_export($find_agent, true), 'info');
                                $agent_data = [
                                    'agent_super_id' => $find_agent['agent_id'],
                                    'agent_one_id' => $find_agent['agent_one_id'],
                                    'agent_two_id' => $find_agent['agent_two_id'],
                                    'agent_three_id' => 0,
                                    'is_agent_share' => 1,
                                ];
                                switch ($agent_level){
                                    case 3:
                                        $agent_data['agent_three_id'] = $find_agent['id'];
                                        break;
                                    case 2:
                                        $agent_data['agent_two_id'] = $find_agent['id'];
                                        break;
                                    case 1:
                                        $agent_data['agent_one_id'] = $find_agent['id'];
                                        break;
                                    case 0:
                                        $agent_data['agent_super_id'] = $find_agent['id'];
                                        break;
                                }

                                \db('member')->where('id', $member_id)->update($agent_data);
                                Log::record('代理商二维码推广步骤完成', 'info');
                                break;
                        }
                    }
                }
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "SCAN":
                $content = "欢迎你关注“小猪圈”，我们以“轻松生活，快乐猪圈”为理念，以“降低使用与迭代成本 ，解决租赁信用体系问题”为思路，为企业和个人提供低值易耗品、分时、免押租赁服务，并提供产品维护、迭代与管理服务 ；从而提升社会资源的利用效率；你只需为使用付费，实现小投入高效率，为你打造轻松生活生态圈。\n 回复“0元租”了解免租活动详情！";
                //$content = "扫描场景 ".$object->EventKey;
                break;
            case "CLICK":
                switch ($object->EventKey) {
                    case "COMPANY":
                        $content = "小猪圈提供互联网相关产品与服务。";
                        break;
                    default:
                        $content = "点击菜单：" . $object->EventKey;
                        break;
                }
                break;
            case "LOCATION":
                $content = "上传位置：纬度 " . $object->Latitude . ";经度 " . $object->Longitude;
                break;
            case "VIEW":
                $content = "跳转链接 " . $object->EventKey;
                break;
            default:
                $content = "receive a new event: " . $object->Event;
                break;
        }
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
        switch ($object->Content) {
            case "你好":
                $content = "你好，详情请致电：4001-139-391 ,\n小猪圈客服8:30-22:30在线为你服务！";
                break;
            case "客服":
                $content = array("MediaId" => '8FgoF2yUpAbuE8Jhv6KAt0rk0VWm_uVJ5Gt4fVybpag');
                //http://mmbiz.qpic.cn/mmbiz_jpg/b9u6pDaoK02BQGJUqEJgxfYGvwEu0o2iaKzayu761RMUmY6GWvEIcxSfZhJfhQVsc1ms0HzvGbicjzegA0X5NsVA/0?wx_fmt=jpeg
                break;
            case "0元租":
                $content = array("MediaId" => '8FgoF2yUpAbuE8Jhv6KAt0rk0VWm_uVJ5Gt4fVybpag');
                //http://mmbiz.qpic.cn/mmbiz_jpg/b9u6pDaoK02BQGJUqEJgxfYGvwEu0o2iaKzayu761RMUmY6GWvEIcxSfZhJfhQVsc1ms0HzvGbicjzegA0X5NsVA/0?wx_fmt=jpeg
                break;
            case "0元租机":
                $content = array();
                $content[] = array("Title" => "0元租机", "Description" => "集满28个赞“高端家电”，0元即可抱回家！", "PicUrl" => "http://xiaozhuquan.oss-cn-shenzhen.aliyuncs.com/uploads/20181109/030cc6c648ee7e00cca03831cb4aec7c.jpg", "Url" => 'http://h5.91xzq.com/leasehold.html');
                break;
            case "图文":
            case "单图文":
                $content = array();
                $content[] = array("Title" => "单图文标题", "Description" => "单图文内容", "PicUrl" => "http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" => "http://m.cnblogs.com/?u=txw1958");
                break;
            case "多图文":
                $content = array();
                $content[] = array("Title" => "多图文1标题", "Description" => "", "PicUrl" => "http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" => "http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title" => "多图文2标题", "Description" => "", "PicUrl" => "http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" => "http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title" => "多图文3标题", "Description" => "", "PicUrl" => "http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" => "http://m.cnblogs.com/?u=txw1958");
                break;
            default:
                //$content = '[捂脸]' . $object->Content;
                $content = "你好，详情请致电：4001-139-391 ，\n小猪圈客服8:30-22:30在线为你服务！";
                break;
        }
        if (is_array($content)) {
            if (isset($content[0]['PicUrl'])) {
                $result = $this->transmitNews($object, $content);
            } else if (isset($content['MusicUrl'])) {
                $result = $this->transmitMusic($object, $content);
            }else {
                $result = $this->transmitImage($object, $content);
            }
        } else {
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId" => '8FgoF2yUpAbuE8Jhv6KAt0rk0VWm_uVJ5Gt4fVybpag');
//        $content = array("MediaId" => $object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：" . $object->Location_X . "；经度为：" . $object->Location_Y . "；缩放级别为：" . $object->Scale . "；位置为：" . $object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)) {
            $content = "你刚才说的是：" . $object->Recognition;
            $result = $this->transmitText($object, $content);
        } else {
            $content = array("MediaId" => $object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId" => $object->MediaId, "ThumbMediaId" => $object->ThumbMediaId, "Title" => "", "Description" => "");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：" . $object->Title . "；内容为：" . $object->Description . "；链接地址为：" . $object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if (!is_array($newsArray)) {
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item) {
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //日志记录
    private function logger($log_content)
    {
        if (isset($_SERVER['HTTP_APPNAME'])) {   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        } else if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") { //LOCAL
            $max_size = 10000;
            $log_filename = "log.xml";
            if (file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)) {
                unlink($log_filename);
            }
            file_put_contents($log_filename, date('H:i:s') . " " . $log_content . "\r\n", FILE_APPEND);
        }
    }


    //获取关注者列表
    public function get_user_list($next_openid = NULL)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->access_token . "&next_openid=" . $next_openid;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }

    //获取用户基本信息
    public function get_user_info($openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->access_token . "&openid=" . $openid . "&lang=zh_CN";
        $res = $this->https_request($url);
        return json_decode($res, true);
    }

    //创建菜单
    public function create_menu($data)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    //发送客服消息，已实现发送文本，其他类型可扩展
    public function send_custom_message($touser, $type, $data)
    {
        $msg = array('touser' => $touser);
        switch ($type) {
            case 'text':
                $msg['msgtype'] = 'text';
                $msg['text'] = array('content' => urlencode($data));
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        return $this->https_request($url, urldecode(json_encode($msg)));
    }

    //生成参数二维码
    public function create_qrcode($scene_type, $scene_id)
    {
        switch ($scene_type) {
            case 'QR_LIMIT_SCENE': //永久
                $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": ' . $scene_id . '}}}';
                break;
            case 'QR_SCENE':       //临时
                $data = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' . $scene_id . '}}}';
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        $result = json_decode($res, true);
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($result["ticket"]);
    }

    //创建分组
    public function create_group($name)
    {
        $data = '{"group": {"name": "' . $name . '"}}';
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    //移动用户分组
    public function update_group($openid, $to_groupid)
    {
        $data = '{"openid":"' . $openid . '","to_groupid":' . $to_groupid . '}';
        $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    //上传多媒体文件
    public function upload_media($type, $file)
    {
        $data = array("media" => "@" . dirname(__FILE__) . '\\' . $file);
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->access_token . "&type=" . $type;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    //地理位置逆解析
    public function location_geocoder($latitude, $longitude)
    {
        $url = "http://api.map.baidu.com/geocoder/v2/?ak=B944e1fce373e33ea4627f95f54f2ef9&location=" . $latitude . "," . $longitude . "&coordtype=gcj02ll&output=json";
        $res = $this->https_request($url);
        $result = json_decode($res, true);
        return $result["result"]["addressComponent"];
    }

    //https请求（支持GET和POST）
    protected function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /** 发送模板消息*/
    public function sendTemplateMessage($data)
    {
        if (!$this->access_token ) return false;
        $result = http_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=ACCESS_TOKEN' . $this->access_token, json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    //获取accesstoken
    private function getAccessToken()
    {
        // 获取缓存
        $access = Cache::get('access_token');
        // 缓存不存在-重新创建
        if (empty($access)) {
            // 获取 access token
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";
            $accessToken = httpGet($url);
//            $accessToken = file_get_contents($url);
            $accessToken = json_decode($accessToken);
            // 保存至缓存
            $access = $accessToken->access_token;
            Cache::set('access_token', $access, 7000);
        }
        return $access;
    }
}

?>