<?php
// Hàm postIndex dùng để lấy dữ liệu từ form POST
function postIndex($index, $value = "")
{
    // Nếu biến $_POST[$index] chưa được gửi lên thì trả về giá trị mặc định $value
    if (!isset($_POST[$index])) return $value;
    // Nếu có thì trả về dữ liệu sau khi loại bỏ khoảng trắng thừa ở đầu/cuối
    return trim($_POST[$index]);
}

// Lấy dữ liệu từ form với các trường tương ứng
$username   = postIndex("username");   // Lấy tên đăng nhập
$password1  = postIndex("password1");  // Lấy mật khẩu lần 1
$password2  = postIndex("password2");  // Lấy mật khẩu lần 2 (nhập lại)
$name       = postIndex("name");       // Lấy họ tên
$sm         = postIndex("submit");     // Lấy giá trị nút submit
$thong_tin =  postIndex("thongtin");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Lab6_1</title>
	<style>
        /* Định dạng khung form */
        fieldset {
            width: 50%;
            margin: 100px auto;
        }
        /* Định dạng khối thông tin phản hồi */
        .info {
            width: 600px;
            color: #006;
            background: #6FC;
            margin: 0 auto
        }
    </style>
</head>

<body>
	<fieldset>
		<legend style="margin:0 auto">Thông tin đăng ký</legend>
		<form action="4_1a.php" method="post" enctype="multipart/form-data">
			<table align="center">
				<tr>
					<td>Tên đăng nhập:</td>
					<td><input type="text" name="username"
							value="<?php echo $username; ?>"></td>
				</tr>
				<tr>
					<td>Mật khẩu:</td>
					<td><input type="password" name="password1" /></td>
				</tr>
				<tr>
					<td>Nhập lại mật khẩu:</td>
					<td><input type="password" name="password2" /></td>
				</tr>
				<tr>
					<td>Họ Tên:</td>
					<td><input type="text" name="name" value="<?php echo $name; ?>" /></td>
				</tr>
                <tr>
                    <td>Thong tin: </td>
                    <td><textarea name="thongtin" value="<?php echo $thong_tin; ?>"></textarea></td>
                </tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="submit" name="submit"></td>
				</tr>
			</table>
		</form>
	</fieldset>
	<?php

	// Nếu nút submit được nhấn
if ($sm != "") {
    $err = ""; // Biến chứa thông báo lỗi

    // Kiểm tra các điều kiện hợp lệ
    if (strlen($username) < 6)      $err .= " Username ít nhất phải 6 ký tự!<br>";
    if ($password1 != $password2)   $err .= "Mật khẩu và mật khẩu nhập lại không khớp. <br>";
    if (strlen($password1) < 8)     $err .= "Mật khẩu phải ít nhất 8 ký tự.<br>";
    if (str_word_count($name) < 2)  $err .= "Họ tên phải chứa ít nhất 2 từ ";
    //1.loai bo cac the html trong thong tin
    $thong_tin = strip_tags($thong_tin);
    //2 thay the ca ky tu xuong dong (\n) = </br>
    $thong_tin= nl2br($thong_tin);
    //3 them \ trc cac ky tu nhay don ' dung addslashes
    $thong_tin_escaped =addslashes($thong_tin);
    //4 loai bo cac ky tu \ trc ca ky tu dac biet
    $thong_tin_escaped=stripslashes($thong_tin_escaped);
    ?>
        <div class="info">
            <?php
            // Nếu có lỗi thì in ra lỗi
            if ($err != "") echo $err;
            else{
                $password_md5 =md5($password1);// ma bang md5 (128bit) 32 ky tu
                $password_sha1 =sha1($password_md5);// ma gia kkq bang sha1 (160 bit) 40 ky tu
                $password_sha256 =hash('sha256',$password1);
                $password_sha3_256 =hash('sha3-256',$password1);
            
                // Nếu không có lỗi thì in ra thông tin người dùng
                echo "Username: $username <br>";
                // Mã hóa mật khẩu bằng hàm md5 để bảo mật
                echo "Mật khẩu đã mã hóa Md5:" . $password_md5 . "<br>";
                // ma hoa mat khau bang ham SHA1
                echo "Mật khẩu đã mã hóa SHA1:" . $password_sha1 . "<br>";
                echo "<hr>";
                echo "Mật khẩu đã mã hóa SHA256:" . $password_sha256 . "<br>";
                echo "Mật khẩu đã mã hóa SHA3-256:" . $password_sha3_256 . "<br>";
                echo"<hr>";
                // In ra họ tên, mỗi từ viết hoa chữ cái đầu
                echo "Họ tên: " . ucwords($name);
                echo "Thong tin da xu ly: $thong_tin_escaped";
            }
            ?>
        </div>
    <?php
}
?>
</body>
</html>