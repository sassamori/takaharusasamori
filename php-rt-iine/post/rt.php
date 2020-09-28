<!--  RTをする処理 -->
<?php
session_start();
require('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $posts = $db->prepare('SELECT members.name, members.picture, posts.* FROM members,posts WHERE members.id=posts.member_id AND posts.id=?');
    $posts->execute(array($_GET['id']));
    $post = $posts->fetch();

    $rt = $db->prepare('INSERT INTO posts SET message=?,member_id=?,rt_flag=1,rt_member_id=?,rt_post_id=?,created=NOW()');
    $rt->execute(array($post['message'],$post['member_id'],$_SESSION['id'],$_GET['id']));

    header('Location: index.php');
    exit();
}

?>