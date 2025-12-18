<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Quản lý loại sách</title>
    <style>
        /* Khung chứa nội dung chính */
        #container {
            width: 600px;
            margin: 0 auto;
            /* căn giữa trang */
        }
    </style>
</head>

<body>
    <div id="container">

        <!-- Form nhập dữ liệu loại sách -->
        <form action="lab8_3.php" method="post">
            <table>
                <tr>
                    <td>Mã loại:</td>
                    <td><input type="text" name="cat_id" /></td>
                </tr>
                <tr>
                    <td>Tên loại:</td>
                    <td><input type="text" name="cat_name" /></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" name="sm" value="Insert" />
                    </td>
                </tr>
            </table>
        </form>

        <?php
        // ------------------- KẾT NỐI CSDL -------------------
        try {
            // Tạo đối tượng PDO kết nối đến database 'bookstore' với user 'root'
            $pdh = new PDO("mysql:host=localhost; dbname=bookstore", "root", "");
            // Thiết lập bộ mã UTF-8 để hiển thị tiếng Việt đúng
            $pdh->query("set names 'utf8'");
        } catch (Exception $e) {
            // Nếu kết nối thất bại thì báo lỗi và dừng chương trình
            echo $e->getMessage();
            exit;
        }

        // ------------------- XỬ LÝ THÊM LOẠI SÁCH -------------------
        if (isset($_POST["sm"])) { // kiểm tra nếu người dùng bấm nút Insert
            // Câu lệnh SQL thêm loại sách mới với tham số
            $sql = "insert into category(cat_id, cat_name) values(:cat_id, :cat_name)";
            // Mảng ánh xạ tham số với dữ liệu nhập từ form
            $arr = array(":cat_id" => $_POST["cat_id"], ":cat_name" => $_POST["cat_name"]);
            // Chuẩn bị và thực thi câu lệnh
            $stm = $pdh->prepare($sql);
            $stm->execute($arr);
            $n = $stm->rowCount(); // số dòng bị ảnh hưởng

            // Thông báo kết quả
            if ($n > 0) echo "Đã thêm $n loại ";
            else echo "Lỗi thêm ";
        }
        //--------------xoa loai sach-------------
        if (isset($_GET['delete'])) {
            $cat_id = $_GET['delete'];
            $sql = "delete from category where cat_id = :cat_id";

            $stm = $pdh->prepare($sql);
            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_STR);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                echo "Đã xóa loại sách với mã: $cat_id";
            } else {
                echo "Lỗi xóa loại sách.";
            }
        }
        //------------sua loai sach----------------
        if (isset($_POST['update'])) {
            $cat_id = $_POST['cat_id'];
            $cat_name = $_POST['cat_name'];
            $sql = "update category set cat_name = :cat_name where cat_id = :cat_id";
            $stm = $pdh->prepare($sql);
            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_STR);
            $stm->bindParam(':cat_name', $cat_name, PDO::PARAM_STR);
            $stm->execute();
            echo "Loại sách đã được cập nhật!";
        }

        // ------------------- PHÂN TRANG -------------------
        // Xác định số lượng bản ghi trên mỗi trang
        $records_per_page = 3; // Hiển thị 3 loại sách mỗi trang

        // Tính toán số trang
        $total_records_query = "SELECT COUNT(*) FROM category";
        $total_records_result = $pdh->query($total_records_query);
        $total_records = $total_records_result->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);

        // Lấy trang hiện tại từ URL, mặc định là trang 1
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1; // Tránh trường hợp số trang nhỏ hơn 1

        // Tính toán vị trí bắt đầu của các bản ghi trong trang hiện tại
        $start_from = ($page - 1) * $records_per_page;

        // Truy vấn các bản ghi cần hiển thị cho trang hiện tại
        $stm = $pdh->prepare("SELECT * FROM category LIMIT :start_from, :records_per_page");
        $stm->bindParam(':start_from', $start_from, PDO::PARAM_INT);
        $stm->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
        $stm->execute();
        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        ?>


        <!-- Hiển thị danh sách loại sách -->
        <table border="1">
            <tr>
                <td>STT</td>
                <td>Ma loai</td>
                <td>Ten loai</td>
                <td>Thao tac</td>
            </tr>
            <?php
            $stt = 1;
            foreach ($rows as $row) {
                echo '<tr>';
                echo "<td>" . $stt . "</td>";
                echo "<td>" . $row['cat_id'] . "</td>";
                echo "<td>" . $row['cat_name'] . "</td>";
                echo "<td>
                    <a href='lab8_3.php?delete=" . $row['cat_id'] . "' onclick='return confirm(\"Bạn có chắc chắn muốn xóa loại sách này?\")'>Xóa</a>
                    <a href='lab8_3_update.php?cat_id=" . $row['cat_id'] . "'>Sửa</a>
                </td>";
                echo "</tr>";
                $stt++;
            }
            ?>
        </table>
        <div class="pagination">
            <?php
            // Hiển thị liên kết phân trang
            if ($page > 1) {
                echo "<a href='lab8_3.php?page=" . ($page - 1) . "'>« Trước</a>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo "<strong>$i</strong>";
                } else {
                    echo "<a href='lab8_3.php?page=$i'>$i</a>";
                }
            }

            if ($page < $total_pages) {
                echo "<a href='lab8_3.php?page=" . ($page + 1) . "'>Sau »</a>";
            }
            ?>
        </div>
    </div>