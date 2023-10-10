
## 設定

次の２つが必要です

- config.php

```
<?php

$config = [
    'client_id' => '',
    'client_secret' => '',
    'team'  => '',
    'channel' => 'CAAAAAAAAAA',
    'channels => [
        'CAAAAAAAAAA' => [],
        'CBBBBBBBBBB' => [],
    ],
];
```

- env.php

```
<?php

$env = [
    'baseuri' => 'http://localhost:50080',
];
```


## 起動

```
docker-compose up
```

ブラウザで http://localhost:50080 を開く
