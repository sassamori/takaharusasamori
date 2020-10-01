<!-- 再RTをする処理 -->
<?php
session_start();
require('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $re_rt_info = $db->prepare(
        'UPDATE posts,rts SET delete_flag=0,rt_delete_flag=0 WHERE posts.id=rts.post_id AND posts.id=?');
    $re_rt_info->execute(array($_GET['id']));

    $rt_rt_post = $db->prepare('UPDATE posts SET delete_flag=0 WHERE posts.rt_post_id=? LIMIT 1');
    $rt_rt_post->execute(array($_GET['id']));

    header('Location: index.php');
    exit();
}
?>