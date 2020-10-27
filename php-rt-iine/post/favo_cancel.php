<!-- favoを取り消す処理 -->
<?php
session_start();
require_once('dbconnect.php');

$favo_records = $db->prepare('SELECT * FROM favos WHERE delete_flag=0 AND post_id=?');
$favo_records->execute(array($_GET['id']));
$favo_record = $favo_records->fetch();

if(isset($_GET['id']) && $_SESSION['id'] == $favo_record['pushing_member_id']){
    $favo_cancel = $db->prepare('UPDATE favos SET delete_flag=1 WHERE post_id=?');
    $favo_cancel->execute(array($_GET['id']));

    header('Location: index.php');
    exit();
}