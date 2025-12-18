<?php
$a=2; $b=1;
$phan_nguyen= (int)($a/$b);
$phan_du=$a%$b;
echo "a= $a va b=$b<br>";
echo "Phan nguyen a va b la: $phan_nguyen<br>";
echo "Phan du a va b la: $phan_du";
?>