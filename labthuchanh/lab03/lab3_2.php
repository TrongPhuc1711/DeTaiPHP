<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab 3_2</title>
</head>

<body>
<?php

function cong($a,$b)
{
	return $a+$b;
}
echo "tinh hai so:<br> ";
$a=2; $b=2;
echo "a=$a <br> b=$b <br> ";
$tong=cong($a,$b);
echo "Cong 2 so: $tong<br>";
swap($x,$y);
function swap(&$a,&$b)
{
	$t=$a;
	$a=$b;
	$b=$t;
}
$x=1;
$y=2;
echo "x=$x <br> y=$y <br> ";
swap($x,$y);
echo "Sau khi swap: <br>";
echo "x=$x; y=$y";
?>
</body>
</html>