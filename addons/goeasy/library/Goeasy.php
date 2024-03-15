<?php

namespace addons\goeasy\library;

use think\Config;

/**
 * GoEasy消息推送
 */
class Goeasy
{
    protected $config = [];
    public $error = '';

    public function __construct()
    {
        $this->config = get_addon_config('goeasy');
    }

    /**
     * 发送给前台用户
     * @param string||int $userId  ('common'=> 广播消息，发送给所有用户)
     * @param mixed $content
     * @return bool
     */
    public function sendToUser($userId, $content)
    {
        $channel = $this->config['appFlag'] . '.user.' . $userId;
        $result = $this->sendGoeasy($channel, $content);
        return $result;
    }

    /**
     * 发送给后台管理员
     * @param string||int $adminId  ('common'=> 广播消息，发送给所有管理员)
     * @param mixed $content
     * @return bool
     */
    public function sendToAdmin($adminId, $content)
    {
        $channel = $this->config['appFlag'] . '.admin.' . $adminId;
        $result = $this->sendGoeasy($channel, $content);
        return $result;
    }

    /**
     * goeasy通用发送函数
     * @param string $channel 发送频道
     * @param mixed $content  发送内容
     * @return bool
     */
    public function sendGoeasy($channel, $content)
    {
        $uri = 'http://' . $this->config['resthost'] . '/publish';
        $appKey = $this->config['account'] == 'free' ? $this->config['commonkey'] : $this->config['restkey'];
        $params = [
            'appkey'  => $appKey,
            'channel' => $channel,
            'content' => json_encode($content)
        ];
        $curl = curl_init ();
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        if ($result->code != 200) {
            $this->error = $result->code . ':' . $result->content;
            return false;
        }
        return true;
    }

    /**
     * 生成GoEasy-OTP
     * @return string
     */
    public static function goEasyOTP(){
        $key = get_addon_config('goeasy')['secretkey'];
        list($mtime, $stime) = explode(' ', microtime());
        $text = '000' . round(($mtime + $stime) * 1000);
        if (PHP_VERSION >= '7.1.0') {
            $cryptText = openssl_encrypt($text, 'AES-128-ECB', $key, 3);
        } else {
            $cryptText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB);
        }
        $OTP = base64_encode($cryptText);
        return $OTP;
    }
}
