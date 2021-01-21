<?php
namespace app\common\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\common\Constant;
/**
 * [中间件] 拦截未登录的Session
 */
class AuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $session = $request->session();
        $action = $request->action;
        $skip = (strcasecmp($action, 'Login') === 0) || (strcasecmp($action, 'checkLogin') === 0);
        // 拦截条件：token不存在 & 非登录操作
        if (!$skip && !$session->get(Constant::Session_Token_Key)) {
            return redirect('/page/login.html');
        }
        return $next($request);
    }
}
