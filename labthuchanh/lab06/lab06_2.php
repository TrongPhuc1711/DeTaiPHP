<?php
// Hàm lấy dữ liệu từ form POST
function postIndex($index, $value = "")
{
    // Nếu biến $_POST[$index] chưa tồn tại thì trả về giá trị mặc định $value
    if (!isset($_POST[$index])) return $value;
    // Nếu có thì trả về dữ liệu sau khi loại bỏ khoảng trắng thừa
    return trim($_POST[$index]);
}

// Hàm lấy phần mở rộng (extension) của file
function getExt($file)
{
    // Tách tên file thành mảng dựa vào dấu chấm "."
    $arr = explode(".", $file);
    // Nếu không có dấu chấm (tức là không có phần mở rộng) thì trả về chuỗi rỗng
    if (Count($arr) < 2) return "";
    // Ngược lại, trả về phần tử cuối cùng của mảng (phần mở rộng)
    return $arr[Count($arr) - 1];
}

// Hàm tạo mật khẩu ngẫu nhiên
function getPasswordRandom($n = 8) // $n là độ dài password mặc định là 8
{
    // Chuỗi ký tự cho phép sử dụng để tạo mật khẩu
    $s = "abcdefghijkmlnopqrstuvxyz0123456789@#$%^&*";
    // Trộn ngẫu nhiên chuỗi $s và lấy $n ký tự đầu tiên
    return substr(str_shuffle($s), 0, $n);
}

// Lấy dữ liệu từ nút submit trong form
$sm = postIndex("submit");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ...>
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
        <legend style="margin:0 auto">Thông tin </legend>
        <!-- Form gửi dữ liệu bằng phương thức POST -->
        <form action="lab06_2.php" method="post" enctype="multipart/form-data">
            <table align="center">
                <tr>
                    <td>Chọn 1 file</td>
                    <td><input type="file" name="file" /> <!-- Ô chọn file -->
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="submit" name="submit"> <!-- Nút submit -->
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>
    <?php

    // Nếu nút submit được nhấn
    if ($sm != "") {
    ?>
        <div class="info">
            <?php
            // Kiểm tra xem có file được upload và không có lỗi
            if (isset($_FILES["file"]) && ($_FILES["file"]["error"] == 0))
                // Lấy phần mở rộng của file (chuyển về chữ thường)
                $ext = strtolower(getExt($_FILES["file"]["name"]));
            else $ext = ""; // Nếu không có file thì phần mở rộng rỗng

            // Tạo mật khẩu ngẫu nhiên dài 9 ký tự
            $pass = getPasswordRandom(9);

            // In ra kết quả: phần mở rộng file và mật khẩu ngẫu nhiên
            echo "Phần mở rộng file là: $ext <br> Password ngẫu nhiên: $pass ";
            ?>
        </div>
    <?php
    }
    ?>
</body>

</html>
