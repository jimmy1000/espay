<?php

namespace addons\goeasy;

use think\Addons;

/**
 * Goeasy
 */
class Goeasy extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    public function configInit(&$config)
    {
        $addonConfig = get_addon_config('goeasy');
        $goeasyConfig = [
            'cdnhost'  => $addonConfig['cdnhost'],
            'logger'   => $addonConfig['logger'],
            'frontend' => $addonConfig['frontend'],
            'backend'  => $addonConfig['backend']
        ];

        if ($addonConfig['account'] == 'free') {
            $goeasyConfig['subkey'] = $addonConfig['subscribekey'];
        } else {
            $goeasyConfig['subkey'] = $addonConfig['clientkey'];
            $goeasyConfig['otp'] = \addons\goeasy\library\Goeasy::goEasyOTP();
        }

        $goeasyConfig['userChannelCommon'] = $addonConfig['appFlag'] . '.user.common';
        $goeasyConfig['adminChannelCommon'] = $addonConfig['appFlag'] . '.admin.common';

        if ($uid = cookie('uid')) {
            $goeasyConfig['userChannelClient'] = $addonConfig['appFlag'] . '.user.' . $uid;
        }
        if ($admin = session('admin')) {
            $goeasyConfig['adminChannelClient'] = $addonConfig['appFlag'] . '.admin.' . $admin['id'];
        }

        $config['goeasy'] = $goeasyConfig;
    }

}
