<?php

require_once '../Garapon/Garapon.php';

$garapon = new CoEdo\Garapon\Garapon();
$results = $garapon->login()->request->connection ?: 'error';
var_dump($results);
