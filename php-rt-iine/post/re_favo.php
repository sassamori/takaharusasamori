<?php
session_start();
require('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $re_favo = $db->prepare('UPDATE favos SET delete_flag=0 WHERE post_id=? AND pushing_member_id=?');
    $re_favo->execute(array($_GET['id'],$_SESSION['id']));

    header('Location: index.php');
    exit();
}
?>