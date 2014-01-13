Garapon TV APIs
---------------

## How To Use

### Install

```sh
cd app/vendors
git clone git@github.com:coedo/garapon4php.git Garapon
mv developer_info.json.sample developer_info.json
```

```
{"user_id":"login_user_id","password":"md5sum for password","developer_id":"garapon developer id"}
```

ガラポンTVのIPアドレス等がすでに分かっている場合は以下のように

```
{
    "user_id": "login_user_id",
    "password": "md5sum for password",
    "developer_id": "garapon developer id",
    "global_ip": "xxx.xxx.xx.xx",
    "port": "12345"
}
```


### Example

```php
<?php

require_once '../Garapon/Garapon.php';

$garapon = new \CoEdo\Garapon\Garapon();
$results = $garapon->search('favorite', array(
    'rank' => 'all',
));
var_dump($garapon->response->success);
var_dump($results);

$results = $garapon->search('EPG', ['key' => '地方裁判所']);
var_dump($results);
```

### Methods

- search($type, $data = array(), $options = array())
  - string $type
    - 'EPG', 'Caption', 'Program', 'Favorite'
  - array $data
    - Postするデータ
  - array $options
    - オプション
