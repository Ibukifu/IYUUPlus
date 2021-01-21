<?php
/**
 * 返回IYUU当前版本号
 */
function IYUU_VERSION():string
{
    return '2.0.0';
}

/**
 * 数据目录
 * @return string
 */
function db_path():string
{
    return base_path() . DIRECTORY_SEPARATOR . 'db';
}

/**
 * 计划任务目录
 */
function cron_path():string
{
    return runtime_path() . DIRECTORY_SEPARATOR . 'crontab';
}

/**
 * 微信推送 爱语飞飞
 * @param string $text
 * @param string $desp
 * @return false|string
 */
function ff($text = '', $desp = '')
{
    $token = env('IYUU', '');
    $desp = ($desp=='')?date("Y-m-d H:i:s") :$desp;
    $postdata = http_build_query(array(
        'text' => $text,
        'desp' => $desp
    ));
    $opts = array('http' =>	array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
    ));
    $context  = stream_context_create($opts);
    $result = file_get_contents('http://iyuu.cn/'.$token.'.send', false, $context);
    return $result;
}

/**
 * 获取全局唯一的UUID
 * @param int $pid
 * @return string
 */
function getUUID(int $pid = 0):string
{
    if (function_exists('posix_getpid')) {
        $pid = posix_getpid();
    }
    return sprintf('pid%d_%d_%s', $pid, mt_rand(1, 9999), uniqid());
}

/**
 * CLI打印调试
 * @param $data
 * @param bool $echo
 * @return string|null
 */
function cli($data, $echo = true)
{
    $str = '----------------------------------------date:'.date("Y-m-d H:i:s").PHP_EOL;
    if (is_bool($data)) {
        $show_data = $data ? 'true' : 'false';
    } elseif (is_null($data)) {
        $show_data = 'null';
    } else {
        $show_data = print_r($data, true);
    }
    $str .= $show_data;
    $str .= '----------------------------------------memory_get_usage:'.memory_get_usage(true).PHP_EOL;
    if ($echo) {
        echo $str;
        return null;
    }
    return $str;
}

/**
 * 粗略验证字符串是否为IYUU的token
 * @param string $token
 * @return bool
 */
function check_token($token = ''):bool
{
    return (strlen($token) < 60) && (strpos($token, 'IYUU') === 0) && (strpos($token, 'T') < 15);
}

/**
 * 是否win平台
 * @return bool
 */
function isWin():bool
{
    return \DIRECTORY_SEPARATOR === '\\';
}

/**
 * 对布尔型进行格式化
 * @param mixed $value 变量值
 * @return boolean/string 格式化后的变量
 */
function booleanParse($value)
{
    $rs = $value;

    if (!is_bool($value)) {
        if (is_numeric($value)) {
            $rs = ($value + 0) > 0 ? true : false;
        } elseif (is_string($value)) {
            $rs = in_array(strtolower($value), ['ok', 'true', 'success', 'on', 'yes', '(ok)', '(true)', '(success)', '(on)', '(yes)']) ? true : false;
        } else {
            $rs = $value ? true : false;
        }
    }

    return $rs;
}