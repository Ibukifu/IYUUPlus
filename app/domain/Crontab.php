<?php
namespace app\domain;

use app\common\Config as Conf;
use app\common\Constant;
use app\domain\Config as domainConfig;

/**
 * 计划任务相关
 */
class Crontab
{
    // 定时任务目录
    public static $cron_dir = 'cron_dir';

    // 任务运行状态目录
    public static $run_dir = 'run_dir';

    // 任务执行进程目录
    public static $pid_dir = 'pid_dir';

    // 任务锁目录
    public static $lock_dir = 'lock_dir';

    // 任务日志记录目录
    public static $log_dir = 'log_dir';

    // 定时任务文件名后缀
    const cron_suffix = '.crontab';

    // 任务进程后缀
    const pid_suffix = '.pid';

    // 任务锁后缀
    const lock_suffix = '.lock';

    // php程序所在完整路径,例如/usr/local/php/bin/php,设置完整路径才能开机启动
    public static $exec_path = PHP_BINARY;

    // 管理员用户名,用户名密码都为空字符串时说明不用验证
    public static $adminName = '';

    // 管理员密码,用户名密码都为空字符串时说明不用验证
    public static $adminPassword = '';

    /**
     * linux系统的crontab任务永远在第1秒执行,且添加定时任务后的1分钟之内是不会执行该任务(即使语法上完全满足)
     * @var mixed
     */
    const cron_minute = '%s %s %s %s %s';
    const cron_second = '%s %s %s %s %s %s';

    /**
     * where可能的值
     */
    const WHERE = [
        'day','day_n','hour','hour_n','minute','minute_n','second','second_n','week','month'
    ];

    /**
     * 构造方法
     */
    public function __construct()
    {
    }

    /**
     * 进程启动时执行
     */
    public static function onWorkerStart()
    {
        // 初始化目录
        $sys_dir = [self::$cron_dir, self::$run_dir, self::$pid_dir, self::$lock_dir, self::$log_dir];
        array_walk($sys_dir, function ($v, $k){
            $dir = cron_path() . DIRECTORY_SEPARATOR . $v;
            !is_dir($dir) and mkdir($dir, '0777', true);
        });

        // 设置php二进制文件
        self::setPhpPath();

        // 初始化计划任务文件
        $cron = Conf::get(domainConfig::filename['crontab'], Constant::config_format, []);
        array_walk($cron, function ($v, $k){
            self::createConfHock($v);
        });
    }

    /**
     * 设置PHP二进制文件
     * @param string $path
     */
    public static function setPhpPath($path = '')
    {
        if (!$path) $path = PHP_BINARY;
        self::$exec_path = $path;
    }

    /**
     * 创建计划任务 钩子
     * @param array $param
     */
    public static function createConfHock(array &$param)
    {
        $param['startTime'] = $param['startTime'] ?? time();
        $param['crontab'] = self::parseCron($param);
        $param['command'] = self::parseCommand($param);
        if (isset($param['switch']) && booleanParse($param['switch'])) {
            self::writeCronFile($param['uuid'], $param);
        } else {
            self::deleteConfHock($param['uuid']);
        }
    }

    /**
     * 删除计划任务 钩子
     * @param string $uuid      uuid或文件名
     * @return bool
     */
    public static function deleteConfHock(string $uuid)
    {
        if (empty($uuid)) {
            return false;
        }
        $file_name = self::getFilePath($uuid, self::$cron_dir, self::cron_suffix);
        clearstatcache();
        if (is_file($file_name)) {
            return @unlink($file_name);
        }
        return true;
    }

    /**
     * 转换为Linux的Crontab语法
     * @param array $param      数据
     * array(
     *      'where' => ''
     *      'weeks' => ''
     *      'day' => ''
     *      'hour' => ''
     *      'minute' => ''
     * )
     * @return string
     *   0    1    2    3    4    5
     *   *    *    *    *    *    *
     *   -    -    -    -    -    -
     *   |    |    |    |    |    |
     *   |    |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
     *   |    |    |    |    +----- month (1 - 12)
     *   |    |    |    +------- day of month (1 - 31)
     *   |    |    +--------- hour (0 - 23)
     *   |    +----------- min (0 - 59)
     *   +------------- sec (0-59)
     */
    public static function parseCron(array $param):string
    {
        $cron = '';
        $where = isset($param['where']) ? $param['where'] : null;       //条件
        $weeks = isset($param['weeks']) ? $param['weeks'] : null;       //星期
        $day   = isset($param['day']) ? $param['day'] : null;           //天
        $hour  = isset($param['hour'])   ? $param['hour'] : null;       //时
        $minute= isset($param['minute']) ? $param['minute'] : null;     //分
        $second= isset($param['second']) ? $param['second'] : '*';      //秒
        if ($where === null || !in_array($where, self::WHERE)) {
            throw new \InvalidArgumentException('Invalid cron param where');
        }

        //TODO：参数验证

        switch ($where) {
            case 'day':         //每天
                $cron = sprintf(self::cron_minute, $minute, $hour, '*', '*', '*');
                break;
            case 'day_n':       //N天
                $cron = sprintf(self::cron_minute, $minute, $hour, '*/'.$day, '*', '*');
                break;
            case 'hour':        //每小时
                $cron = sprintf(self::cron_minute, $minute, '*', '*', '*', '*');
                break;
            case 'hour_n':      //N小时
                $cron = sprintf(self::cron_minute, $minute, '*/'.$hour, '*', '*', '*');
                break;
            case 'minute':      //每分钟
                $cron = sprintf(self::cron_minute, '*', '*', '*', '*', '*');
                break;
            case 'minute_n':    //N分钟
                $cron = sprintf(self::cron_minute, '*/'.$minute, '*', '*', '*', '*');
                break;
            case 'second':      //每秒
                $cron = sprintf(self::cron_second, '*', '*', '*', '*', '*', '*');
                break;
            case 'second_n':    //N秒
                $cron = sprintf(self::cron_second, '*/'.$second, '*', '*', '*', '*', '*');
                break;
            case 'week':        //每周
                $cron = sprintf(self::cron_minute, $minute, $hour, '*', '*', $weeks);
                break;
            case 'month':       //每月
                $cron = sprintf(self::cron_minute, $minute, $hour, '*', $day, '*');
                break;
        }

        return $cron;
    }

    /**
     * 解析计划任务命令
     * @param array $param
     * @return string
     */
    public static function parseCommand(array $param):string
    {
        return 'date';
    }

    /**
     * 创建计划任务文件
     * @param string $filename      文件名
     * @param mixed  $data          数据
     * @return bool|int             结果
     */
    public static function writeCronFile(string $filename, $data)
    {
        $file_name = self::getFilePath($filename, self::$cron_dir, self::cron_suffix);
        clearstatcache();
        if (file_exists($file_name)) {
            chmod($file_name, 0777);
        }
        $str = json_encode($data, JSON_UNESCAPED_UNICODE);
        $writeLen = file_put_contents($file_name, $str);

        return $writeLen === 0 ? false : $writeLen;
    }

    /**
     * 获取文件路径
     * @param string $filename  文件名
     * @param string $dir       子目录
     * @param string $suffix    扩展名
     * @return string           文件的完整路径
     */
    public static function getFilePath(string $filename = '', string $dir = 'cron_dir', string $suffix = '.crontab'):string
    {
        clearstatcache();
        $_dir = cron_path() . DIRECTORY_SEPARATOR . $dir;
        if(!is_dir($_dir))
        {
            mkdir($_dir, '0777', true);
        }
        return $_dir . DIRECTORY_SEPARATOR  . $filename . $suffix;
    }

    /**
     * 异步执行命令
     * @descr 原理为php的程序执行函数后台执行
     * @param string $cmd
     */
    public static function exec($cmd = '')
    {
        if(DIRECTORY_SEPARATOR === '\\')
        {
            pclose(popen('start /B '.$cmd, 'r'));
        } else {
            pclose(popen($cmd, 'r'));
        }
    }

    /**
     * 异步执行命令
     * @descr 原理为php的程序执行函数后台执行
     * @param string $cmd
     * @param string $logFile
     */
    public static function execute($cmd = '', $logFile = '')
    {
        $logFile = cron_path() . DIRECTORY_SEPARATOR . self::$log_dir . DIRECTORY_SEPARATOR . $logFile .'.log';
        if(DIRECTORY_SEPARATOR === '\\')
        {
            pclose(popen('start /B '.$cmd.' >> '.$logFile, 'r'));
        } else {
            pclose(popen($cmd.' >> '.$logFile.' 2>&1 &', 'r'));
            //exec($cmd.' >> '.$logFile.' 2>&1 &');
        }
    }
}
