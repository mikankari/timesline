<?php

require_once 'config.php';

session_start();
if (! isset($_SESSION['state']) || $_SESSION['state'] != 2) {
    header('Location: auth.php');
}

$users = json_decode(file_get_contents('https://slack.com/api/users.list?' . http_build_query([
    'token'     => $_SESSION['access_token'],
])));
foreach ($users->members as $item) {
    if (isset($members[$item->id])) {
        continue;
    }
    $members[$item->id] = $item;
}

$times = json_decode(file_get_contents('https://slack.com/api/channels.history?' . http_build_query([
    'token'     => $_SESSION['access_token'],
    'channel'   => $config['channel'],
    'oldest'    => (new DateTime('today'))->format('U'),
    'count'     => 1000,
])));

//var_dump($users);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="1800">
<title>#times line</title>
<link rel="stylesheet" href="//cdn.jsdelivr.net/normalize/7.0.0/normalize.css" />
<style>
    body {
        font-family: sans-serif;
        font-size: 87.5%;
    }
    .container {
        margin: 0 auto;
        width: 640px;
    }
    .clearfix > div {
        float: left;
    }
    .clearfix:after {
        content: "";
        display: block;
        clear: both;
    }
    a {
        color: inherit;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
<?php
if (! empty($_REQUEST['tw'])) {
?>
    .me img {
        border-radius: 50%;
        height: 48px;
    }
    .me textarea {
        vertical-align: bottom;
        width: 489px;
        height: 3em;
        padding: 8px;
        border-radius: 4px;
    }
    .me input[type=submit] {
        vertical-align: bottom;
        color: #fff;
        background: #666;
        border: 0;
        border-radius: 4px;
        padding: 0.5em 1em;
    }
    .message {
        padding: 6px 4px;
        border-bottom: 1px solid #f0f0f0;
    }
    .avatarWrap, .textWrap {
        margin: 4px;
    }
    .textWrap {
        width: 568px;
    }
    .avatar img {
        border-radius: 50%;
    }
    .nameWrap {
        margin-bottom: 4px;
    }
    .name, .screenname, .timestamp {
        display: inline;
    }
    .name {
        font-weight: bold;
    }
    .screenname::before {
        content: "@";
    }
    .timestamp, .screenname {
        font-size: 85.7%;
        color: #999;
    }
<?php
} else {
?>
    .me img {
        border-radius: 4px;
        height: 32px;
    }
    .me textarea {
        vertical-align: bottom;
        width: 505px;
        height: 3em;
        padding: 8px;
        border-radius: 4px;
    }
    .me input[type=submit] {
        vertical-align: bottom;
        color: #fff;
        background: #666;
        border: 0;
        border-radius: 4px;
        padding: 0.5em 1em;
    }
    .message {
        margin: 8px 4px;
    }
    .avatarWrap, .textWrap {
        margin: 4px;
    }
    .textWrap {
        width: 584px;
    }
    .avatar img {
        border-radius: 4px;
        height: 32px;
    }
    .nameWrap {
        margin-bottom: 4px;
    }
    .name {
        display: none;
    }
    .screenname, .timestamp {
        display: inline;
    }
    .screenname {
        font-weight: bold;
    }
    .timestamp {
        font-size: 78.6%;
        color: #999;
    }
<?php
}
?>
</style>
</head>

<body>
<script type="text/javascript">
    setInterval(() => {
        if (document.getElementById('message').value === '') {
            location.reload();
        }
    }, 1800000);
</script>
<div class="container">
    <div class="me">
        <div class="message clearfix">
            <div class="avatarWrap">
                <div class="avatar"><img src="<?php print $members[$_SESSION['user_id']]->profile->image_48; ?>" alt="avatar"></div>
            </div>
            <div class="textWrap">
                <form action="post.php" method="post">
                    <textarea name="text" id="message" placeholder=""></textarea>
                    <input type="submit">
                </form>
            </div>
        </div>
    </div>
    <div class="timeline">
<?php
        foreach ($times->messages as $item) {
            $timestamp = new DateTime('@' . $item->ts);
?>
        <div class="message clearfix">
            <div class="avatarWrap">
                <div class="avatar"><img src="<?php print $members[$item->user]->profile->image_48; ?>" alt="avatar"></div>
            </div>
            <div class="textWrap">
                <div class="nameWrap">
                    <div class="name"><?php print $members[$item->user]->real_name; ?></div>
                    <div class="screenname"><?php print $members[$item->user]->name; ?></div>
                    <div class="timestamp">
                        <a href="https://sencorp-group.slack.com/archives/<?php print $config['channel']; ?>/p<?php print $item->ts; ?>">
                            <?php print $timestamp->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('H:i'); ?></div>
                        </a>
                </div>
                <div class="text"><?php print str_replace("\n", '<br>', $item->text); ?></div>
            </div>
        </div>
<?php
        }
?>
    </div>
</div>
</body>
</html>
