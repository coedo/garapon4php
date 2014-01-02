<?php
namespace CoEdo\Garapon;


class Response {

    /**
     * @var string $error_message
     */
    public $error_message = '';

    /**
     * @var array $results
     */
    public $results = array();

    /**
     * @var int $status
     */
    public $status;

    /**
     * @var bool $success
     */
    public $success = false;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}