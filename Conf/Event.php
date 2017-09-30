<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:06
 */

namespace Conf;


use Core\AbstractInterface\AbstractEvent;
use Core\Component\Di;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;
use Core\Component\Logger;
use Core\Swoole\SwooleHttpServer;

class Event extends AbstractEvent
{
    function frameInitialize()
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    function frameInitialized()
    {
        // TODO: Implement frameInitialized() method.
        date_default_timezone_set('Asia/Shanghai');
    }


    /**
     * @param \swoole_server $server
     */
    function beforeWorkerStart(\swoole_server $server)
    {
        // TODO: Implement beforeWorkerStart() method.
        $this->createTable();
        $this->config = Config::getInstance();

        //添加websocket回调事件
        $server->on("open", function (\swoole_websocket_server $server, $request) {
            $user = [
                'fd' => $request->fd,
                'name' => Config::getInstance()->getConf("name")[array_rand(Config::getInstance()->getConf("name"))].$request->fd,
                'avatar' => Config::getInstance()->getConf("avatar")[array_rand(Config::getInstance()->getConf("avatar"))]
            ];
            $this->table->set($request->fd, $user);

            $server->push($request->fd, json_encode(
                    array_merge(['user' => $user], ['all' => $this->allUser()], ['type' => 'openSuccess'])
                )
            );
            $this->pushMessage($server, "热烈欢迎".$user['name']."进入聊天室", 'open', $request->fd);
        });
        $server->on("message", function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) {
            $this->pushMessage($server, $frame->data, 'message', $frame->fd);
        });

        $server->on("close", function (\swoole_http_server $server, $fd) {
            $user = $this->table->get($fd);
            $this->pushMessage($server, $user['name']."离开聊天室", 'close', $fd);
            $this->table->del($fd);
        });
        //添加websocket回调事件结束
    }

    /**
     * 遍历发送消息
     *
     * @param \swoole_websocket_server $server
     * @param $message
     * @param $messageType
     * @param int $frameFd
     */
    private function pushMessage(\swoole_websocket_server $server, $message, $messageType, $frameFd)
    {
        $message = htmlspecialchars($message);
        $datetime = date('Y-m-d H:i:s', time());
        $user = $this->table->get($frameFd);
        foreach ($this->table as $row) {
            if ($frameFd == $row['fd']) {
                continue;
            }
            $server->push($row['fd'], json_encode([
                    'type' => $messageType,
                    'message' => $message,
                    'datetime' => $datetime,
                    'user' => $user
                ])
            );
        }
    }

    private function allUser()
    {
        $users = [];
        foreach ($this->table as $row) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * 创建内存表
     */
    private function createTable()
    {
        $this->table = new \swoole_table(1024);
        $this->table->column('fd', \swoole_table::TYPE_INT);
        $this->table->column('name', \swoole_table::TYPE_STRING, 255);
        $this->table->column('avatar', \swoole_table::TYPE_STRING, 255);
        $this->table->create();
    }

    function onStart(\swoole_server $server)
    {
        // TODO: Implement onStart() method.
    }

    function onShutdown(\swoole_server $server)
    {
        // TODO: Implement onShutdown() method.
    }

    function onWorkerStart(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
    }

    function onWorkerStop(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStop() method.
    }

    function onRequest(Request $request, Response $response)
    {
        // TODO: Implement onRequest() method.
    }

    function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction)
    {
        // TODO: Implement onDispatcher() method.
    }

    function onResponse(Request $request,Response $response)
    {
        // TODO: Implement afterResponse() method.
    }

    function onTask(\swoole_server $server, $taskId, $workerId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    function onFinish(\swoole_server $server, $taskId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    function onWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code)
    {
        // TODO: Implement onWorkerError() method.
    }
}
