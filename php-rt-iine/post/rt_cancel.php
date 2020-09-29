<!-- RTを取り消す処理 -->
<?php
session_start();
require('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $relations = $db->prepare('UPDATE posts,rts SET delete_flag=1,rt_delete_flag=1 WHERE posts.rt_post_id=rts.post_id AND posts.id=?');
    $relations->execute(array($_GET['id']));

    header('Location: index.php');
    exit();
}
?>