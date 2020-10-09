<!-- favoを取り消す処理 -->
<?php
session_start();
require_once('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $favo_cancel = $db->prepare('UPDATE favos SET delete_flag=? WHERE post_id=?');
    $favo_cancel->execute(array(1,$_GET['id']));

    header('Location: index.php');
    exit();
}