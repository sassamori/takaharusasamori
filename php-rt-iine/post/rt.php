<!--  RTをする処理 -->
<?php
session_start();
require_once('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $posts = $db->prepare('SELECT members.name, members.picture, posts.* FROM members,posts WHERE members.id=posts.member_id AND posts.id=?');
    $posts->execute(array($_GET['id']));
    $post = $posts->fetch();

    $rt_posts = $db->prepare('INSERT INTO posts SET message=?,member_id=?,rt_flag=1,rt_member_id=?,rt_post_id=?,created=NOW()');
    $rt_posts->execute(array($post['message'],$post['member_id'],$_SESSION['id'],$_GET['id']));

    $rted_tweet = $db->prepare('UPDATE posts SET rted_flag=1,rt_member_id=? WHERE id=?');
    $rted_tweet->execute(array($_SESSION['id'],$_GET['id']));

    $rt_info = $db->prepare('INSERT INTO rts SET post_id=?,member_id=?,rt_member_id=?,created=NOW()');
    $rt_info->execute(array($_GET['id'],$post['member_id'],$_SESSION['id']));

    $rts = $db->query('SELECT * FROM rts');
    $rt = $rts->fetch();

    $update = $db->prepare('UPDATE rts SET rt_post_id=? WHERE post_id=');

    header('Location: index.php');
    exit();
}

?>