<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>lab 2_5</title>
</head>

<body>
<?php
    echo "<h3>Require khi chay khong thay file se bao loi va dung chuong trinh, chuong trinh se khong xuat ra x</h3>";
	require("lab2_5a.php");
	require("lab2_5bb.php");
    require("lab2_5bb.php");
	echo "<B>Ket qua x van la =30<br></B>";
	if(isset($x))
		echo "Giá trị của x là: $x";
	else
		echo "Biến x không tồn tại";
    ;
?>

</body>
</html>