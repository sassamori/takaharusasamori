<!-- RTを取り消す処理 -->
<?php
session_start();
require_once('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    //元ツイートのdelete_flagを1にする
    $posts = $db->prepare('UPDATE posts SET delete_flag=1 WHERE id=?');
    $posts->execute(array($_GET['id']));
    //準備
    $relations_before = $db->query(
        'SELECT posts.id AS important_id,posts.*,rts.* FROM posts,rts WHERE posts.rt_post_id=rts.post_id');
    $relations_before_all = $relations_before->fetchall();
    //リツイートのdelete_flagを1にして、リツイート情報のrt_delete_flagも1にする
    foreach($relations_before_all as $relation_before){
        if($_GET['id'] == $relation_before['rt_post_id'] && $_SESSION['id'] == $relation_before['rt_member_id']){
            $relations = $db->prepare(
                'UPDATE posts,rts SET delete_flag=1,rt_delete_flag=1 WHERE posts.rt_post_id=rts.post_id AND posts.id=?');
            $relations->execute(array($relation_before['important_id']));
        }
    }
    header('Location: index.php');
    exit();
}