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

### Example

```php
$garapon = new Garapon();
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
