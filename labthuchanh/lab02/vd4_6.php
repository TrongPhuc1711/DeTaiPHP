<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>lab 2_5</title>
</head>

<body>
<?php
	include("lab2_5a.php");
	include("lab2_5bb.php");
    include("lab2_5bb.php");
	echo "<B>vi include dau tien x=10, include thu 2 va 3: x=x+10 ma 2 lan=>x=30<br></B>";
	if(isset($x))
		echo "Giá trị của x là: $x";
	else
		echo "Biến x không tồn tại";
    echo "<br> Khi xoa file lab2_5b.php thi khi chay file se bi loi vi khong tim thay file lab2_5b.php nhung van xuat ra x = 10"
?>
</body>
</html>