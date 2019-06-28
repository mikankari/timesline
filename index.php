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
    textarea {
        background-color: transparent;
        color: inherit;
    }
    a {
        color: #999;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    .text a {
        color: #006999;
    }
    body.dark {
        background-color: #333;
        color: #e6e6e6;
    }
    .dark textarea {
        border-color: #ccc;
    }
    .dark .text a {
        color: #00AEFF;
    }
    .reaction {
        font-size: 0.8rem;
        margin-right: 5px;
        margin-top: 8px;
        display: inline-block;
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
        border-bottom: 1px solid #f2f2f2;
    }
    .dark .message {
        border-bottom-color: #404040;
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

<body class="<?php print ! empty($_REQUEST['dark']) ? 'dark' : '' ?>">
<script type="text/javascript">
    setInterval(() => {
        if (document.getElementById('message').value === '') {
            location.reload();
        }
    }, 1800000);

    // 長押し対策
    let isFirstPost = true;

    document.onkeydown = (e) => {
        if (isPressedSubmitKey(e)
            && isFirstPost
            && document.getElementsByName('text')[0].value !== ''
        ) {
            isFirstPost = false;
            document.forms.posting.submit();
        }
    };

    const isPressedSubmitKey = (keyEvent) => {
        return keyEvent.key === 'Enter' && (keyEvent.ctrlKey || keyEvent.metaKey);
    };
</script>
<div class="container">
    <div class="me">
        <div class="message clearfix">
            <div class="avatarWrap">
                <div class="avatar"><img src="<?php print $members[$_SESSION['user_id']]->profile->image_48; ?>" alt="avatar"></div>
            </div>
            <div class="textWrap">
                <form action="post.php" method="post" name="posting">
                    <textarea name="text" id="message" placeholder=""></textarea>
                    <input type="submit">
                </form>
            </div>
        </div>
    </div>
    <div class="timeline">
<?php
        foreach ($times->messages as $item) {
            $timestamp = new DateTime('@' . substr($item->ts, 0, strpos($item->ts, '.')));
            $text = str_replace("\n", '<br>', $item->text);
            $text = preg_replace('/<(https?\:\/\/[^<> ]+)>/', '<a href="$1" target="_blank">$1</a>', $text);
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
                <div class="text"><?php print $text; ?></div>
                <?php
                    if (isset($item->reactions)) {
                        foreach ($item->reactions as $reaction) {
                            echo '<div class="reaction">' . ':' . $reaction->name . ':' . $reaction->count . '</div>';
                        }
                    }
                ?>
            </div>
        </div>
<?php
        }
?>
    </div>
</div>
</body>
</html>
