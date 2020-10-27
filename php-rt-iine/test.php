<?php
$array1 = [1,2,3,4,5];
$array2 = [6,7,8,9,0];

foreach($array1 as $array01){
    foreach($array2 as $array02){
        echo $array01.$array02;
        echo "<br>";
    }
}
?>