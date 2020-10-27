<?php
$file = $_FILES['picture'];
?>

ファイル名（name）：<?php print($file['name']); ?><br>
ファイルタイプ（type）：<?php print($file['type']); ?><br>
アップロードされたファイル（tmp_name）：<?php print($file['tmp_name']); ?><br>
エラー内容（error）：<?php print($file['error']); ?><br>
サイズ（size）：<?php print($file['size']); ?><br>

<?php
$ext = substr($file['name'],-4);
if($ext == '.gif' || $ext == '.jpg' || $ext == '.png'):
    $filePath = './user_img/'.$file['name'];
    $success = move_uploaded_file($file['tmp_name'],$filePath);
?>
    <?php if($success): ?>
        <img src="<?php print($filePath); ?>">
    <?php else: ?>
        ※失敗しました
    <?php endif ?>
<?php else: ?>
※拡張子がアカン
<?php endif ?>