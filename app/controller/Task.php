<?php
namespace app\controller;

use app\domain\Users as domainUsers;
use support\Request;
use support\Response;
use app\domain\Reseed as domainReseed;
use Workerman\Crontab\Crontab;
use Workerman\Crontab\Parser;

class Task extends BaseController
{
    /**
     * 默认控制器
     * @descr 检查未登录重定向
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $url = domainUsers::isLogin($request) ? '/index.html' : '/page/login.html';
        return redirect($url);
    }

    /**
     * 根据参数，解析辅种的站点和下载器
     * @param Request $request
     * @return Response
     */
    public function reseedConfig(Request $request): Response
    {
        $rs = self::RS;
        $uuid = $request->get('uuid');
        return json(domainReseed::configParser($uuid));
    }

    /**
     * 开启|关闭，计划任务
     * @param Request $request
     * @return Response
     */
    public function switch(Request $request): Response
    {
        $rs = self::RS;
        $uuid = $request->get('uuid');
        $switch = $request->get('switch');
        return json(domainReseed::configParser($uuid));
    }

    /**
     * 停止正在执行的计划任务
     * @param Request $request
     * @return Response
     */
    public function stop(Request $request): Response
    {
        $rs = self::RS;
        $uuid = $request->get('uuid');
        return json(domainReseed::configParser($uuid));
    }

    /**
     * 调试接口
     * @param Request $request
     * @return Response
     */
    public function test(Request $request): Response
    {
        $parser = new Parser();
        return json($parser->parse('*/10 * * * * *'));
    }
}
