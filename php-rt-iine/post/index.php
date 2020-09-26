<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 >time()){
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
}else{
    header('Location: login.php');
    exit();
}

if(!empty($_POST)){
    if($_POST['message'] != ''){
        $message = $db->prepare('INSERT INTO posts SET member_id=?,message=?,reply_post_id=?,created=NOW()');
        $message->execute(array(
            $member['id'],
            $_POST['message'],
            $_POST['reply_post_id']
        ));

        header('Location: index.php');
        exit();
    }
}

$page = $_REQUEST['page'];
if($page == ''){
    $page = 1;
}
$page = max($page,1);

$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page,$maxPage);

$start = ($page - 1) * 5;

$posts = $db->prepare('SELECT members.name, members.picture, posts.* FROM members,posts WHERE members.id=posts.member_id ORDER BY posts.created DESC LIMIT ?,5');
$posts->bindParam(1,$start,PDO::PARAM_INT);
$posts->execute();

$favos = $db->query('SELECT * FROM favos');
$favos_all = $favos->fetchall();
$count = $db->query('SELECT COUNT(*) AS cnt FROM favos');
$favo_count = $count->fetch();

if(isset($_REQUEST['res'])){
    $response = $db->prepare('SELECT members.name, members.picture, posts.* FROM members,posts WHERE members.id=posts.member_id AND posts.id=? ORDER BY posts.created DESC');
    $response->execute(array($_REQUEST['res']));

    $table = $response->fetch();
    $message = '@'.$table['name'].' '.$table['message'];
}

function h($value){
    return htmlspecialchars($value,ENT_QUOTES);
}

function makeLink($value){
    return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",'<a href="\1\2">\1\2</a>',$value);
}

?>

<body>
<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
<form action="" method="post">
    <dl>
        <dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
        <dd>
            <textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
            <input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>">
        </dd>
    </dl>
    <div>
        <input type="submit" value="投稿する" />
    </div>
</form>
<?php foreach($posts as $post): ?>
<div class="msg">
    <img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
    <p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
    <p class="day">
        <a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
        <?php if($post['reply_post_id'] > 0): ?>
            <a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元メッセージ</a>
        <?php endif ?>
        <?php
        $not_favo_count = 0;
        foreach($favos_all as $favo):
        ?>
            <?php if($post['id'] == $favo['post_id'] && $member['id'] == $favo['pushing_member_id'] && $favo['delete_flag'] == 0): ?>
                [いいね済][<a href="favo_cancel.php?id=<?php echo h($post['id']); ?>">いいね取り消し</a>]
            <?php else: ?>
                <?php $not_favo_count++; ?>
                <?php if($post['id'] == $favo['post_id'] && $member['id'] == $favo['pushing_member_id'] && $favo['delete_flag'] == 1): ?>
                    [<a href="re_favo.php?id=<?php echo h($post['id']); ?>">再いいね</a>]
                    <?php break; ?>
                <?php elseif($not_favo_count == $favo_count['cnt']): ?>
                    [<a href="favo.php?id=<?php echo h($post['id']); ?>">いいね</a>]
                <?php endif ?>
            <?php endif ?>
        <?php endforeach ?>
        <?php if($member['id'] == $post['member_id']): ?>
            <br>[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color:#F33;">削除</a>]
        <?php endif ?>
    </p>
</div>
<?php endforeach ?>

<?php if($page > 1): ?>
<a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a>
<?php else: ?>
前のページへ
<?php endif ?>
<?php if($page < $maxPage): ?>
<a href="index.php?page=<?php print($page + 1); ?>">前のページへ</a>
<?php else: ?>
次のページへ
<?php endif ?>

</body>