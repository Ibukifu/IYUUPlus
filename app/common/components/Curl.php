<?php
namespace app\common\components;

use app\common\Constant;
use Curl\Curl as ICurl;

/**
 * 单例Curl
 */
class Curl
{
    /**
     * 单例
     * @var null
     */
    protected static $_instance = null;
    /**
     * 私有化构造函数，避免外部new
     */
    private function __construct()
    {
    }

    /**
     * 单例
     * @param bool $reset
     * @return ICurl
     */
    public static function one($reset = true)
    {
        if (self::$_instance === null) {
            self::$_instance = new ICurl();
        }
        // 重置
        if ($reset) {
            self::$_instance->reset();
        }
        // 设置UserAgent
        self::$_instance->setUserAgent(Constant::UserAgent);
        return self::$_instance;
    }
}
