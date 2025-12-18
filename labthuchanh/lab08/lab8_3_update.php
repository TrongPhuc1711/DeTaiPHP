<?php
// ------------------- KẾT NỐI CSDL -------------------
try {
    $pdh = new PDO("mysql:host=localhost; dbname=bookstore", "root", "");
    $pdh->query("set names 'utf8'");
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

// ------------------- LẤY THÔNG TIN LOẠI SÁCH CẦN SỬA -------------------
if (isset($_GET['cat_id'])) {
    $cat_id = $_GET['cat_id'];
    // Lấy thông tin loại sách từ cơ sở dữ liệu
    $stm = $pdh->prepare("SELECT * FROM category WHERE cat_id = :cat_id");
    $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_STR);
    $stm->execute();
    $category = $stm->fetch(PDO::FETCH_ASSOC);
}

// ------------------- XỬ LÝ CẬP NHẬT LOẠI SÁCH -------------------
if (isset($_POST['update'])) {
    $cat_id = $_POST['cat_id'];
    $cat_name = $_POST['cat_name'];
    // Cập nhật loại sách trong cơ sở dữ liệu
    $sql = "UPDATE category SET cat_name = :cat_name WHERE cat_id = :cat_id";
    $stm = $pdh->prepare($sql);
    $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_STR);
    $stm->bindParam(':cat_name', $cat_name, PDO::PARAM_STR);
    $stm->execute();
    echo "Loại sách đã được cập nhật!";
    header("Location: lab8_3.php"); // chuyển hướng về trang danh sách
    exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chỉnh Sửa Loại Sách</title>
    <style>
        #container {
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div id="container">
        <h3>Chỉnh Sửa Loại Sách</h3>
        <?php if (isset($category)) { ?>
            <form action="lab8_3_update.php" method="post">
                <input type="hidden" name="cat_id" value="<?php echo $category['cat_id']; ?>" />
                <table>
                    <tr>
                        <td>Mã loại:</td>
                        <td><input type="text" name="cat_id" value="<?php echo $category['cat_id']; ?>" readonly /></td>
                    </tr>
                    <tr>
                        <td>Tên loại:</td>
                        <td><input type="text" name="cat_name" value="<?php echo $category['cat_name']; ?>" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" name="update" value="Cập nhật" />
                        </td>
                    </tr>
                </table>
            </form>
        <?php } else { ?>
            <p>Không tìm thấy loại sách.</p>
        <?php } ?>
        <br>
        <a href="lab8_3.php">Quay lại</a>
    </div>
</body>

</html>
