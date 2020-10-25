<!-- RTを取り消す処理 -->
<?php
session_start();
require_once('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    //元ツイートのdelete_flagを1にする
    $posts = $db->prepare('UPDATE posts SET delete_flag=1 WHERE id=?');
    $posts->execute(array($_GET['id']));
    //準備
    $relations = $db->prepare(
        'UPDATE posts left JOIN rts ON posts.rt_post_id=rts.post_id AND posts.rt_member_id = rts.rt_member_id
                    SET posts.delete_flag=1,rts.rt_delete_flag=1,posts.modified=now(),rts.modified=now() 
                    WHERE posts.rt_post_id = ? AND rts.rt_member_id = ?');
    $relations->execute([$_GET['id'],$_SESSION['id']]);
    header('Location: index.php');
    exit();
}