<?php

namespace CoEdo\Garapon;


class Response {

    /**
     * @var int $status
     */
    public $status;

    /**
     * @var bool $success
     */
    public $success = false;

    /**
     * @var string $error_message
     */
    public $error_message = '';

    /**
     * @var array $results
     */
    public $results = array();

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}