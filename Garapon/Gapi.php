<?php

namespace CoEdo\Garapon;


class Gapi {

    public function __construct($configFilePath = null)
    {
        $this->request = new Request();
        $this->response = $this->request->response;
        $this->settings($configFilePath);
        return $this;
    }

    protected function _buildMethod($type, $prefix = '')
    {
        $result = $prefix . strtoupper($type[0]) . strtolower(substr($type, 1));
        return $result;
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

    protected function _get($method, $data, $options)
    {
        return $this->request->get($method, $data, $options);
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
        $this->webRequest = new Request($this::GARAPON_WEB_AUTH_URL);
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

    protected function _post($method, $data, $options)
    {
        return $this->request->post($method, $data, $options);
    }

    public function settings($path = null)
    {
        $defaultPath = 'developer_info.json';
        if (!$path)
        {
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
            'api_version' => $this::API_VERSION,
            'api_dir' => $this::API_DIR,
        );
        $settings += $defaults;
        if (empty($settings['user_id']) || empty($settings['password']) || empty($settings['developer_id']))
        {
            throw new \Exception('Invalid config file');
        }
        return $settings;
    }

}