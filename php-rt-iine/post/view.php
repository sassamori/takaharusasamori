<?php
session_start();
require_once('dbconnect.php');

if(empty($_REQUEST['id'])){
    header('Location: index.php');
    exit();
}

$posts = $db->prepare('SELECT members.name, members.picture, posts.* FROM members,posts WHERE members.id=posts.member_id AND posts.id=? ORDER BY posts.created DESC');
$posts->execute(array($_REQUEST['id']));
?>

<p>&laquo;<a href="index.php">一覧に戻る</a></p>
<?php if($post = $posts->fetch()): ?>
    <div class="msg">
        <img src="member_picture/<?php echo htmlspecialchars($post['picture'],ENT_QUOTES); ?>" width="48" height="48" alt="<?php echo htmlspecialchars($post['name'],ENT_QUOTES); ?>">
        <p><?php echo htmlspecialchars($post['message'],ENT_QUOTES); ?><span class="name">(<?php echo htmlspecialchars($post['name'],ENT_QUOTES); ?>)</span></p>
        <p><?php echo htmlspecialchars($post['created'],ENT_QUOTES); ?></p>
<?php else: ?>
    <p>その投稿は削除されたかURLミスです</p>
<?php endif ?>