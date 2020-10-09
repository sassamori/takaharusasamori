<?php
session_start();
require_once('dbconnect.php');
require_once('function_h.php');
require_once('function_makelink.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 >time()){
    $_SESSION['time'] = time();

    $login_members = $db->prepare('SELECT * FROM members WHERE id=?');
    $login_members->execute(array($_SESSION['id']));
    $login_member = $login_members->fetch();
}else{
    header('Location: login.php');
    exit();
}

//この辺をいじって、RT投稿に利用する
if(!empty($_POST)){
    if($_POST['message'] !== ''){
        $message = $db->prepare('INSERT INTO posts SET member_id=?,message=?,reply_post_id=?,created=NOW()');
        $message->execute(array(
            $login_member['id'],
            $_POST['message'],
            $_POST['reply_post_id']
        ));

        header('Location: index.php');
        exit();
    }
}

$page = $_GET['page'] ?? 1;
$page = max($page,1);

$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts WHERE NOT (rt_flag=1 AND delete_flag=1)');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page,$maxPage);

$start = ($page - 1) * 5;

$posts = $db->prepare(
    'SELECT members.name, members.picture, posts.*
    FROM members INNER JOIN posts ON members.id=posts.member_id
    WHERE NOT (rt_flag=1 AND delete_flag=1) ORDER BY posts.created DESC LIMIT ?,5'
);
$posts->bindParam(1,$start,PDO::PARAM_INT);
$posts->execute();

//favosテーブルからデータを引き出す
$favos = $db->query('SELECT * FROM favos WHERE NOT delete_flag=1');
$favos_all = $favos->fetchall();
//favosテーブルのレコードをカウントする
$count = $db->query('SELECT COUNT(*) AS cnt FROM favos');
$favo_count = $count->fetch();

//rtsテーブルからデータを引き出す
$rts = $db->query('SELECT * FROM rts');
$rts_all = $rts->fetchall();

if(isset($_REQUEST['res'])){
    $response = $db->prepare(
        'SELECT members.name, members.picture, posts.*
        FROM members,posts
        WHERE members.id=posts.member_id AND posts.id=? ORDER BY posts.created DESC');
    $response->execute(array($_REQUEST['res']));

    $table = $response->fetch();
    $message = '@'.$table['name'].' '.$table['message'];
}

?>

<body>
<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
<form action="" method="post">
    <dl>
        <dt><?php echo h($login_member['name']); ?>さん、メッセージをどうぞ</dt>
        <dd>
            <textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
            <input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>">
        </dd>
    </dl>
    <div>
        <input type="submit" value="投稿する" />
    </div>
</form>
<?php
foreach($posts as $post):
    $rt_counts = $db->prepare('SELECT COUNT(*) as cnt FROM rts WHERE rt_delete_flag=0 AND post_id=?');
    $rt_counts->execute(array($post['id']));
    $rt_count = $rt_counts->fetch();
?>
<div class="msg">
    <img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
    <?php if($post['rt_flag'] == 1): ?>
        〜リツイート〜
    <?php endif ?>
    <p>
        <?php echo makeLink(h($post['message'])); ?>
        （<?php echo h($post['name']); ?>）
        [<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]
    </p>
    <p class="day">
        <a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
        <?php if($post['reply_post_id'] > 0): ?>
            <a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元メッセージ</a>
        <?php endif ?>
        <?php
        $favo_judgments = $db->prepare('SELECT * FROM favos WHERE post_id=? AND pushing_member_id=?');
        $favo_judgments->execute(array($post['id'],$login_member['id']));
        $favo_judgment = $favo_judgments->fetch();
        ?>
            <!-- 該当のポストIDが一致している時、かつ、自分が押したいいねの時に、「いいね済・いいね取り消し」ボタンを表示 -->
            <?php if($favo_judgment): ?>
                [いいね済][<a href="favo_cancel.php?id=<?php echo h($post['id']); ?>">いいね取り消し</a>]
            <?php else: ?>
                [<a href="favo.php?id=<?php echo h($post['id']); ?>">いいね</a>]
            <?php endif ?>

            <!-- リツイートのツイート（rt_flagが1）に対して、自分が押したRTの時、かつ、rt_delete_flagが0の時に、「RT済・RT取り消し」ボタンを表示する -->
            <?php if($login_member['id'] == $post['rt_member_id'] && $post['delete_flag'] == 0 && $post['rted_flag'] == 1): ?>
                [RT済]
                (<?php echo h($rt_count['cnt']); ?>)
                [<a href="rt_cancel.php?id=<?php echo h($post['id']); ?>">RT取り消し</a>]
            <!-- 上記以外のリツイートのツイート（rt_flag=1）だったら何も表示しない -->
            <?php elseif($post['rt_flag'] == 1): ?>
            <!-- 削除フラグが1（delete_flag=1）だったら再RTと表示する -->
            <?php elseif($post['delete_flag'] == 1): ?>
                [<a href="re_rt.php?id=<?php echo h($post['id']); ?>">再RT</a>]
                (<?php echo h($rt_count['cnt']); ?>)
            <!-- 上記3つ以外だったらRTと表示する -->
            <?php else: ?>
                [<a href="rt.php?id=<?php echo h($post['id']); ?>">RT</a>]
                (<?php echo h($rt_count['cnt']); ?>)
            <?php endif ?>

        <!-- RTカウント -->

        <?php if($login_member['id'] == $post['member_id']): ?>
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
<a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a>
<?php else: ?>
次のページへ
<?php endif ?>

</body>