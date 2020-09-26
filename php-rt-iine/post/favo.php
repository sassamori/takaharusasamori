<?php
session_start();
require('dbconnect.php');

if(isset($_GET['id']) && isset($_SESSION['id'])){
    $favo = $db->prepare('INSERT INTO favos SET post_id=?,pushing_member_id=?,delete_flag=0,created=NOW()');
    $favo->execute(array($_GET['id'],$_SESSION['id']));

    header('Location: index.php');
    exit();
}
?>