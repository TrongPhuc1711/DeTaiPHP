<?php
function showArray($arr)
{
	foreach($arr as $k=>$v)
	{
		echo "<br> $k - $v ";	
	}	
}
$a = array(6, 2, 7, 8, 5); 
$b = array("a"=>4, "b"=>2, "c"=>3, "d"=>8);
$n = array_rand($a);
echo "Phần tử ngẫu nhiên: key=$n , giá trị=".$a[$n];
$c = array_rand($a, 3);
echo "<br> Danh sách 3 phần tử ngẫu nhiên được lấy ra:";
foreach($c as $k)
{
	echo "(key=$k -	value={$a[$k]})";
}

$m=3;
$c = array_rand($b, $m);
echo "<br> Danh sách $m phần tử ngẫu nhiên được lấy ra từ b:";
foreach($c as $k)
{
	echo "(key=$k -	value={$b[$k]})";
}
?><hr />
<?php
$a1= $a; 
rsort($a1);
echo "Mảng a sau khi sắp xếp giam dan (rsort): <br>";
showArray($a1);

$a2= $a; 
arsort($a2);
echo "<br>Mảng a sau khi sắp xếp giam dan khong giu key(arsort): <br>";
showArray($a2);

echo "<hr> Tính tổng ";
$sum_a = array_sum($a);
$sum_b = array_sum($b);
echo "<br> Tổng các giá trị trong mảng a = $sum_a , mảng b= $sum_b ";


?>


