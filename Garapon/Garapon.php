<?php

namespace CoEdo\Garapon;

require_once 'Gapi.php';
require_once 'Request.php';
require_once 'Response.php';

class Garapon extends Gapi
{
    const API_DIR = 'gapi';
    const API_VERSION = 'v3';
    const GARAPON_WEB_AUTH_URL = 'http://garagw.garapon.info/getgtvaddress';

    /**
     * @var array $_map
     */
    protected $_map = array(
        '0' => 'status',
        'ipaddr' => 'ip',
        'gipaddr' => 'global_ip',
        'pipaddr' => 'private_ip',
        'port' => 'port',
        'port2' => 'ts_port',
        'gtvver' => 'version',
    );

    /**
     * @var Request $request
     */
    public $request;

    /**
     * @var Response $response
     */
    public $response;

    /**
     * @var array $settings
     */
    public $settings = array();

    /**
     * @var string $url
     */
    public $url;

    /**
     * @var Request $webRequest
     */
    public $webRequest;

    public function login($force = false)
    {
        if (!$force && $this->isLoggedIn())
        {
            return $this;
        }
        $settings = (array)$this->request->connection + $this->settings;
        $data = array(
            'type' => 'login',
            'loginid' => $settings['user_id'],
            'md5pswd' => $settings['password'],
        );
        $query = array(
            'dev_id' => $settings['developer_id'],
        );
        if (!$this->isGetConnected())
        {
            $this->getConnection();
        }
        $this->request->post('auth', $data, compact('query'));
        $this->_checkStatus(array(
            '0' => 'Status error or empty parameter',
            '100' => 'Login failed',
            '200' => 'Login failed',
        ));
        $this->request->connection->gtvsession = $this->response->results->gtvsession;
        return $this;
    }

    /**
     * @param string $type : 'EPG', 'Caption', 'Program', 'Favorite'
     * @param array $data
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function search($type, $data = array(), $options = array())
    {
        $method = $this->_buildMethod($type, 'search');
        if (!method_exists($this, $method))
        {
            throw new \Exception("Undefined search type: $type");
        }
        return $this->{$method}($data, $options);
    }

    public function searchCaption($data = array(), $options = array())
    {
        $data['s'] = 'c'; // 字幕検索
        return $this->login()->_post('search', $data, $options);
    }

    public function searchEpg($data = array(), $options = array())
    {
        $data['s'] = 'e'; // EPG検索
        return $this->login()->_post('search', $data, $options);
    }

    public function searchFavorite($data = array(), $options = array())
    {
        if (empty($data['rank']))
        {
            throw new \Exception('Required gtvid');
        }
        return $this->login()->_post('search', $data, $options);
    }

    public function searchProgram($data = array(), $options = array())
    {
        if (empty($data['gtvid']))
        {
            throw new \Exception('Required gtvid');
        }
        return $this->login()->_post('search', $data, $options);
    }

}