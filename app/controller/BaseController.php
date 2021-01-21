<?php
namespace app\controller;

use app\common\exception\BusinessException;
use app\common\Constant;

/**
 * 控制器基类
 */
class BaseController
{
    /**
     * 接口返回数据结构
     * @var array
     */
    const RS = Constant::RS;

    public function __construct()
    {
    }

    /**
     * 接口参数
     * @param string $name 接口参数名字
     * @param mixed $value 接口参数解析后的值
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * 获取接口参数
     * @param string $name 接口参数名字
     * @return mixed
     * @throws BusinessException
     */
    public function __get($name) {
        if(!isset($this->$name) || empty($name)) {
            throw new BusinessException(sprintf('$this->%s not null', $name));
        }

        return $this->$name;
    }
}
