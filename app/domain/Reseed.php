<?php
namespace app\domain;

use app\common\Config as Conf;
use app\common\Constant;

/**
 * 辅种相关
 * Class Reseed
 * @package app\domain
 */
class Reseed
{
    /**
     * 根据参数，解析辅种的站点和下载器
     * @param string $uuid
     * @return array
     */
    public static function configParser($uuid = ''):array
    {
        $rs = [
            'sites'   => [],
            'clients' => [],
        ];

        $cronFilename = Config::filename['crontab'];
        $cron = Conf::get($cronFilename, Constant::config_format, []);
        $cron = array_key_exists($uuid, $cron) ? $cron[$uuid] : [];
        //检查使能
        if (isset($cron['switch']) && $cron['switch'] === 'on') {
            //解析站点
            $sites = Conf::get(Config::filename['user_sites'], Constant::config_format, []);
            if (!empty($cron['sites']) && !empty($sites)) {
                $key = $cron['sites'];
                $rs['sites'] = array_filter($sites, function ($v, $k) use ($key) {
                    return array_key_exists($k, $key);
                }, ARRAY_FILTER_USE_BOTH);
            }

            //解析下载器
            $clients = Conf::get(Config::filename['clients'], Constant::config_format, []);
            if (!empty($cron['clients']) && !empty($clients)) {
                $key = $cron['clients'];
                $rs['clients'] = array_filter($clients, function ($k) use ($key) {
                    return array_key_exists($k, $key);
                }, ARRAY_FILTER_USE_KEY);
            }
        }

        return $rs;
    }
}
