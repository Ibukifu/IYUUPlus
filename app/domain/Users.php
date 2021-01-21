<?php
namespace app\domain;

use support\Request;
use app\common\components\Curl;
use app\common\Constant;
use app\common\Config;

/**
 * 用户逻辑操作类 [无状态静态类]
 */
class Users
{
    /**
     * 检查用户Session是否已登录
     * @param Request $request
     * @return bool
     */
    public static function isLogin(Request $request):bool
    {
        $session = $request->session();
        $has = $session->has('token');
        return $has ? true : false;
    }

    /**
     *
     * @param string $token
     * @param Request $request
     * @return array
     * @throws \app\common\exception\BusinessException
     */
    public static function checkToken(string $token, Request $request):array
    {
        $curl = Curl::one();
        $api_url = Constant::API_BASE;
        $api_action = Constant::API['sites'];
        $url = sprintf('%s%s?sign=%s&version=%s', $api_url, $api_action, $token, IYUU_VERSION());
        $res = $curl->get($url);
        $rs = json_decode($res->response, true);
        if ($rs['ret'] === 200 && isset($rs['data']['sites']) && is_array($rs['data']['sites'])) {
            $sites = array_column($rs['data']['sites'], null, 'site');
            Config::set('sites', $sites, Constant::config_format);
            Config::set('iyuu', ['iyuu.cn' => $token], Constant::config_format);
            // 验证通过，写入Session
            $session = $request->session();
            $session->set(Constant::Session_Token_Key, $token);
        } else {
            if (($rs['ret'] === 403) && isset($rs['data']['recommend']) && is_array($rs['data']['recommend'])) {
                //用户未绑定合作站点
                $recommend = $rs['data']['recommend'];
                Config::set('recommend', $recommend, Constant::config_format);
                Config::set('iyuu', ['iyuu.cn' => $token], Constant::config_format);
            }
        }

        return $rs;
    }
}
