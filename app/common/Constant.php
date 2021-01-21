<?php
namespace app\common;

/**
 * 全局常量定义
 */
class Constant
{
    const UserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';

    //用户登录后用来保存IYUU token的Session键名
    const Session_Token_Key = 'token';
    /**
     * API定义
     */
    const API_BASE = 'http://api.iyuu.cn';
    const API = [
        'login'   => '/user/login',
        'sites'   => '/api/sites',
        'infohash'=> '/api/infohash',
        'hash'    => '/api/hash',
        'notify'  => '/api/notify',
        'recommend'  =>  '/Api/GetRecommendSites'
    ];

    /**
     * 配置文件默认保存格式
     */
    const config_format = 'json';

    /**
     * 编辑配置时配置文件的键名
     */
    const config_filename = 'config_filename';

    /**
     * 编辑配置时动作的键名
     */
    const action = 'action';

    /**
     * 模拟数据库主键UUID的键名
     */
    const uuid = 'uuid';

    /**
     * 微信消息体定义
     */
    const WechatMsg = [
        'hashCount'			=>	0,		// 提交给服务器的hash总数
        'sitesCount'		=>	0,		// 可辅种站点总数
        'reseedCount'		=>	0,		// 返回的总数据
        'reseedSuccess'		=>	0,		// 成功：辅种成功（会加入缓存，哪怕种子在校验中，下次也会过滤）
        'reseedError'		=>	0,		// 错误：辅种失败（可以重试）
        'reseedRepeat'		=>	0,		// 重复：客户端已做种
        'reseedSkip'		=>	0,		// 跳过：因未设置passkey，而跳过
        'reseedPass'		=>	0,		// 忽略：因上次成功添加、存在缓存，而跳过
        'MoveSuccess'       =>  0,      // 移动成功
        'MoveError'         =>  0,      // 移动失败
    ];

    /**
     * 错误通知消息体
     */
    const ErrorNotify = [
        'sign' => '',
        'site' => '',
        'sid'   => 0,
        'torrent_id'=> 0,
        'error'   => '',
    ];

    /**
     * 接口返回的数据结构
     * @var array
     */
    const RS = [
        'ret'   =>  200,
        'data'  =>  [],
        'msg'   =>  ''
    ];

    /**
     * 全局错误码
     */
}
