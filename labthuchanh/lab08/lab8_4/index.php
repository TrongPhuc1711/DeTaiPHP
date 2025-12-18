<?php
// Nạp các file cấu hình và hàm cần thiết cho ứng dụng
include "config/config.php";              // File cấu hình (ví dụ: thông tin kết nối CSDL)
include ROOT . "/include/function.php";   // File chứa các hàm tiện ích dùng chung

// Đăng ký hàm tự động nạp class (autoload)
// Khi khởi tạo một đối tượng, PHP sẽ tự động tìm và load file class tương ứng
spl_autoload_register("loadClass");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Database!</title>
    <style>
        /* CSS định dạng cho khối hiển thị sách */
        .book {
            width: 250px;
            height: 300px;
            margin: 3px;
            background: #FCC; 
            float: left;    
        }

        /* CSS cho hình ảnh bên trong khối sách */
        div.book img {
            height: 200px;
            margin: 0 10px;
        }
    </style>
</head>

<body>
    <?php
    // Tạo đối tượng kết nối CSDL (class Db sẽ được tự động load từ file Db.class.php)
    $obj = new Db();

    // Thực hiện truy vấn lấy tất cả các dòng trong bảng category
    $rows = $obj->select("select * from category ");

    // Duyệt qua từng dòng kết quả và in ra ID + tên danh mục
    foreach ($rows as $row) {
        echo "<br>" . $row["cat_id"] . "-" . $row["cat_name"];
    }
    echo "<hr>"; 

    // Tạo đối tượng Book (class Book sẽ được load từ file Book.class.php)
    $book = new Book();

    // Lấy ngẫu nhiên 5 quyển sách từ CSDL
    $rows = $book->getRand(5);

    // Duyệt qua từng quyển sách và hiển thị thông tin
    foreach ($rows as $row) {
    ?>
        <div class='book'>
            <!-- Hiển thị ID và tên sách -->
            <?php echo $row["book_id"] . "-" . $row["book_name"]; ?>
            <hr />
            <!-- Hiển thị hình ảnh sách -->
            <img src="image/book/<?php echo $row["img"]; ?>" />
        </div>
    <?php
    }
    ?>
</body>

</html>
