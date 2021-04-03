<?php

require_once 'config.php';

session_start();
if (! isset($_SESSION['state']) || $_SESSION['state'] != 2) {
    exit('require logging in');
}

$times = json_decode(file_get_contents('https://slack.com/api/conversations.history', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => implode(PHP_EOL, [
            'Authorization: Bearer ' . $_SESSION['access_token'],
        ]),
        'content' => http_build_query([
            'channel'   => $config['channel'],
            'oldest'    => (new DateTime('today'))->format('U'),
            'count'     => 1000,
        ]),
    ],
])));

$ts = null;
foreach (array_reverse($times->messages) as $item) {
    if ($item->user === $_SESSION['user_id']) {
        $ts = $item->ts;
        break;
    }
}

$result = json_decode(file_get_contents('https://slack.com/api/chat.postMessage', false, stream_context_create([
    'http' => [
        'method'    => 'POST',
        'header' => implode(PHP_EOL, [
            'Authorization: Bearer ' . $_SESSION['access_token'],
        ]),
        'content'   => http_build_query([
            'channel'   => $config['channel'],
            'text'      => $_REQUEST['text'],
            'thread_ts' => $ts,
        ]),
    ],
])));

if ($result->ok) {
    header('Location: .');
} else {
    print 'failed post';
}

//var_dump($result);
