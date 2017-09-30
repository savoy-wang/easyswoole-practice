<?php

namespace App\Controller\Api;

use Core\AbstractInterface\AbstractController;
use Core\Http\Message\Status;
use Conf\Config;

/**
*
*/
class WebSocket extends AbstractController {
	private $server;

    private $table;

    protected $config;

	public function __construct() {
		$this->createTable();
        $this->config = Config::instance();
	}

    

	/**
     * 创建内存表
     */
    private function createTable() {
        $this->table = new \swoole_table(1024);
        $this->table->column('fd', \swoole_table::TYPE_INT);
        $this->table->column('name', \swoole_table::TYPE_STRING, 255);
        $this->table->column('avatar', \swoole_table::TYPE_STRING, 255);
        $this->table->create();
    }

    function index() {
        $this->server = new Server(Config::getInstance()->getConf("SERVER.LISTEN"), Config::getInstance()->getConf("SERVER.PORT"));

        $this->server->on('open', [$this, 'open']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);

        $this->server->start();
    }


    function open (\swoole_websocket_server $server, $request) {
        $user = [
            'fd' => $request->fd,
            'name' => Config::getInstance()->getConf("SERVER.name")[array_rand(Config::getInstance()->getConf("SERVER.name"))].$request->fd,
            'avatar' => Config::getInstance()->getConf("SERVER.avatar")[array_rand(Config::getInstance()->getConf("SERVER.avatar"))]
        ];
        $this->table->set($request->fd, $user);

        $server->push($request->fd, json_encode(
                array_merge(['user' => $user], ['all' => $this->allUser()], ['type' => 'openSuccess'])
            )
        );
        $this->pushMessage($server, "欢迎".$user['name']."进入聊天室", 'open', $request->fd);
    }

    private function allUser() {
        $users = [];
        foreach ($this->table as $row) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * @param \swoole_websocket_server $server
     * @param $frame
     */
    public function message(\swoole_websocket_server $server, $frame) {
        $this->pushMessage($server, $frame->data, 'message', $frame->fd);
    }

    /**
     * @param \swoole_websocket_server $server
     * @param $fd
     */
    public function close(\swoole_websocket_server $server, $fd) {
        $user = $this->table->get($fd);
        $this->pushMessage($server, $user['name']."离开聊天室", 'close', $fd);
        $this->table->del($fd);
    }

    /**
     * 遍历发送消息
     *
     * @param \swoole_websocket_server $server
     * @param $message
     * @param $messageType
     * @param $frameFd
     */
    private function pushMessage(\swoole_websocket_server $server, $message, $messageType, $frameFd) {
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

    function afterAction() {
        // TODO: Implement afterAction() method.
    }

    function onRequest($actionName) {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null) {
        // TODO: Implement actionNotFount() method.
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    function afterResponse() {
        // TODO: Implement afterResponse() method.
    }
}


