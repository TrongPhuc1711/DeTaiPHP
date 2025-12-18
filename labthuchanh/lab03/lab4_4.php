<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab 4_4</title>
<style>
	#banco{border:solid; padding:15px; background:#E8E8E8}
	#banco .cellBlack{width:50px; height:50px; background:black; float:left; }
	#banco .cellWhite{width:50px; height:50px; background:white; float:left}
	.clear{clear:both}
</style>
</head>

<body>
<?php
/*
bảng cửu chương $n, màu nền $color
- Input: $n là một số nguyên dương (1->10)
		 $color: Tên màu nền.Mặc định là green
- Output: Bảng cửu chương, được xuât trong hàm
*/
function BCC($n, $colorHead,$color1,$color2)
{
	?>
	<table>
	<tr bgcolor="
        <?php 
            echo "$colorHead";
        ?>"><td colspan="3">Bảng cửu chương <?php echo $n;?></td></tr>
	<?php
		for($i=1; $i<=10; $i++)
		{
            if($i%2==0){
            ?>
			<tr bgcolor="<?php echo "$color1"?>"><td><?php echo $n;?></td>
				<td><?php echo $i;?></td>
				<td><?php echo $n*$i;?></td>
			</tr>
			<?php }
            else{
                ?>
                <tr bgcolor="<?php echo "$color2"?>"><td><?php echo $n;?></td>
                    <td><?php echo $i;?></td>
                    <td><?php echo $n*$i;?></td>
                </tr>
                <?php 
            }
		}
		?>
		</table>
	<?php	
}

function BCC2($n, $colorHead,$color1,$color2)
{
	$output = "<table>";
    $output .= "<tr bgcolor='" . $colorHead . "'><td colspan='3'>Bảng cửu chương $n</td></tr>";
    
    for ($i = 1; $i <= 10; $i++) {
        if ($i % 2 == 0) {
            $output .= "<tr bgcolor='" . $color1 . "'><td>$n</td><td>$i</td><td>" . $n * $i . "</td></tr>";
        } else {
            $output .= "<tr bgcolor='" . $color2 . "'><td>$n</td><td>$i</td><td>" . $n * $i . "</td></tr>";
        }
    }

    $output .= "</table>";
    return $output;  // Trả về chuỗi HTML
}
/*
Hàm in ra bàn cờ vua với màu các ô thay đổi và được định nghĩa trong css: cellBlack, cellWhite
- Input: $size: kích thước bàn cờ: là 1 số nguyên dương (mặc định là 8)
- Output: bàn cờ HTML 

*/
function BanCo($size =8)
{
	?>
	<div id="banco">
		<?php
		for($i=1; $i<= $size; $i++)
		{
			for($j=1; $j<= $size; $j++)
			{
				$classCss = (($i+$j) %2)==0?"cellWhite":"cellBlack";
				echo "<div class='$classCss'> $i - $j</div>";
			}
			echo "<div class='clear' />";
			
		}
	?>
	</div>
	<?php
}

function BanCo2($size =8)
{
	$output = "<div id='banco'>";
    
    for ($i = 1; $i <= $size; $i++) {
        for ($j = 1; $j <= $size; $j++) {
            $classCss = (($i + $j) % 2) == 0 ? "cellWhite" : "cellBlack";
            $output .= "<div class='$classCss'> $i - $j</div>";
        }
        $output .= "<div class='clear'></div>";
    }
    
    $output .= "</div>";
    return $output;  // Trả về chuỗi HTML
}
Bcc(6,"blue","green","red");
echo BCC2(6,"gray","pink","blue");	
Banco();
echo BanCo2();
?>
</body>
</html>