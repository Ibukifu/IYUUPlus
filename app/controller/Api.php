<?php
namespace app\controller;

use support\Request;
use support\Response;
use app\common\exception\BusinessException;
use app\common\Config;
use app\common\Constant;
use app\domain\Config as domainConfig;
use app\domain\Users as domainUsers;

class Api extends BaseController
{
    /**
     * 默认控制器
     * @descr 检查未登录重定向
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $url = domainUsers::isLogin($request) ? '/index.html' : '/page/login.html';
        return redirect($url);
    }

    /**
     * 查询用户Session是否已登录
     * @desc 因静态页无法响应301/302状态码，所以加入此接口供前端主动调用
     * @param Request $request
     * @return Response
     */
    public function checkLogin(Request $request): Response
    {
        $rs = self::RS;
        $rs['data'] = [
            'is_login'  => domainUsers::isLogin($request)
        ];

        return json($rs);
    }

    /**
     * 登录
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function Login(Request $request): Response
    {
        $rs = self::RS;
        $token = $request->get('token');
        if (check_token($token)) {
            $rs = domainUsers::checkToken($token, $request);
        } else {
            $rs['ret'] = 403;
            $rs['msg'] = 'Token格式错误！';
        }

        return json($rs);
    }

    /**
     * 退出登录
     * @param Request $request
     * @return Response
     */
    public function Logout(Request $request): Response
    {
        $request->session()->flush();
        return json(self::RS);
    }

    /**
     * 版本信息
     * @param Request $request
     * @return mixed
     */
    public function Version(Request $request): Response
    {
        return json(config());
    }

    /**
     * 获取菜单
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function Menu(Request $request): Response
    {
        $init = Config::get('init', 'json');
        //TODO 前端菜单注入接口
        return json($init);
    }

    /**
     * 配置接口{增、删、改、查}
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function Config(Request $request): Response
    {
        $rs = self::RS;
        $key = Constant::config_filename;
        // 取值优先级：get > post
        $config_filename = $request->get($key) ? $request->get($key) : $request->post($key);   // 值对应( /db/?.ext )这个文件名
        if ($config_filename) {
            $rs = domainConfig::main($config_filename, $request);
        } else {
            $rs['ret'] = 403;
            $rs['msg'] = 'config_filename错误！';
        }
        return json($rs);
    }

    /**
     * 获取站点列表
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function sitesList(Request $request): Response
    {
        $data = Config::get('sites', Constant::config_format);
        $sites = array_keys($data);
        sort($sites);
        return json($sites);
    }

    /**
     * 清理缓存
     * @param Request $request
     * @return Response
     */
    public function Clear(Request $request): Response
    {
        return json(['code' => 1, 'msg' => '辅种缓存清理成功']);
    }
}
