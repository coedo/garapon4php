<?php

namespace CoEdo\Garapon;

require_once 'Request.php';
require_once 'Response.php';

class Garapon
{
    const API_DIR = 'gapi';
    const API_VERSION = 'v3';
    const GARAPON_WEB_AUTH_URL = 'http://garagw.garapon.info/getgtvaddress';

    /**
     * @var array $_map
     */
    private $_map = array(
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

    public function __construct($configFilePath = null)
    {
        $this->request = new Request();
        $this->response = $this->request->response;
        $this->settings($configFilePath);
        return $this;
    }

    protected function _checkStatus($errorMessages, $prefix = '')
    {
        foreach ($this->response->results as $code => $value)
        {
            if (isset($errorMessages[$code]))
            {
                throw new \Exception($prefix . $errorMessages[$code]);
            }
        }
    }

    public function getConnection($force = false)
    {
        if (!$force && ($this->isLoggedIn() || $this->isGetConnected()))
        {
            return $this;
        }
        $data = array(
            'user'      => $this->settings['user_id'],
            'md5passwd' => $this->settings['password'],
            'dev_id'    => $this->settings['developer_id'],
        );
        $this->webRequest = new Request(self::GARAPON_WEB_AUTH_URL);
        $this->webRequest->webRequest($data);
        $this->_getConnection();
        return $this;
    }

    protected function _getConnection()
    {
        if (!$this->webRequest->response->success)
        {
            throw new \Exception('ERROR: ' . $this->webRequest->response->error_message);
        }
        foreach ($this->_map as $before => $after)
        {
            $value = $this->webRequest->response->results->$before;
            unset($this->webRequest->response->results->$before);
            $this->webRequest->response->results->$after = $value;
        }
        $connection = (array)$this->request->connection;
        $results = (array)$this->webRequest->response->results;
        $this->request->connection = (object)($connection + $results + $this->settings);
        unset($this->request->connection->password);
        return $this->request->connection;
    }

    public function isGetConnected()
    {
        return isset($this->request->connection->gtvver);
    }

    public function isLoggedIn()
    {
        return isset($this->request->connection->gtvsession);
    }

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

    public function settings($path = null)
    {
        $defaultPath = 'developer_info.json';
        if (!$path) {
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $defaultPath;
        }
        $settings = $this->_settings($path);
        $this->settings = $settings;
        $this->request->connection += $settings;
        return $this;
    }

    protected function _settings($path)
    {
        if (!file_exists($path))
        {
            throw new \Exception("File not found: $path");
        }
        $json = file_get_contents($path);
        $settings = json_decode($json, true);
        if ($settings == null || !is_array($settings))
        {
            throw new \Exception("Cannot Decode JSON file: $path");
        }
        $defaults = array(
            'user_id' => null,
            'password' => null,
            'developer_id' => null,
            'api_version' => self::API_VERSION,
            'api_dir' => self::API_DIR,
        );
        $settings += $defaults;
        if (empty($settings['user_id']) || empty($settings['password']) || empty($settings['developer_id']))
        {
            throw new \Exception('Invalid config file');
        }
        return $settings;
    }

    public function url($host, $version = null)
    {
        $version = $version ? : $this->version;
        $url = "http://$host/gapi/$version/";
        $this->_gapi->url = $url;
    }
}