<?php

/**
 * Created by PhpStorm.
 * User: YF
 * Date: 16/8/25
 * Time: 上午12:05
 */
namespace Conf;

use Core\Component\Di;
use Core\Component\Spl\SplArray;
use Core\Component\Sys\SysConst;

class Config
{
    private static $instance;
    protected $conf;
    function __construct()
    {
        $conf = $this->sysConf()+$this->userConf();
        $this->conf = new SplArray($conf);
    }
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }
    function getConf($keyPath){
        return $this->conf->get($keyPath);
    }
    /*
            * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
    */
    function setConf($keyPath,$data){
        $this->conf->set($keyPath,$data);
    }

    private function sysConf(){
        return array(
            "SERVER"=>array(
                "LISTEN"=>"0.0.0.0",
                "SERVER_NAME"=>"",
                "PORT"=>9502,
                "RUN_MODE"=>SWOOLE_PROCESS,//不建议更改此项
                "SERVER_TYPE"=>\Core\Swoole\Config::SERVER_TYPE_WEB_SOCKET,//
                'SOCKET_TYPE'=>SWOOLE_TCP,//当SERVER_TYPE为SERVER_TYPE_SERVER模式时有效
                "CONFIG"=>array(
                    'task_worker_num' => 8, //异步任务进程
                    "task_max_request"=>10,
                    'max_request'=>5000,//强烈建议设置此配置项
                    'worker_num'=>8,
                    "log_file"=>Di::getInstance()->get(SysConst::LOG_DIRECTORY)."/eswoole.log",
                    'pid_file'=>Di::getInstance()->get(SysConst::LOG_DIRECTORY)."/pid.pid",
                ),
            ),
            "DEBUG"=>array(
                "LOG"=>1,
                "DISPLAY_ERROR"=>1,
                "ENABLE"=>false,
            ),
            "CONTROLLER_POOL"=>true//web或web socket模式有效
        );
    }

    private function userConf(){
        return array(
            'avatar' => [
                '/public/images/avatar/1.jpg',
                '/public/images/avatar/2.jpg',
                '/public/images/avatar/3.jpg',
                '/public/images/avatar/4.jpg',
                '/public/images/avatar/5.jpg',
                '/public/images/avatar/6.jpg'
            ],
            'name' => [
                '科比',
                '库里',
                'KD',
                'KG',
                '乔丹',
                '邓肯',
                '格林',
                '汤普森',
                '伊戈达拉',
                '麦迪',
                '艾弗森',
                '卡哇伊',
                '保罗'
            ]
        );
    }
}