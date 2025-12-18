<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dang ky</title>
</head>
<body>
    <fieldset>
        <legend>PhucDepTrai</legend>
    <form action="lab5_4_3.php" method="post" enctype="multipart/form-data">
        <label for="ten">Ten dang nhap</label> 
        <input type="text" name="ten" require><br>
        <label for="matkhau">Mat khau:</label> 
        <input type="password" name="matkhau" require ><br>
        <label for="confirm_pass">Nhap lai mat khau:</label> 
        <input type="password" name="confirm_pass" ><br>
        <label for="gioitinh"></label>
        <label for="gioitinh">Gioi tinh:</label><br>
        <input type="radio" name="gioitinh" value="Nam" required>
        <label for="nam">Nam</label><br>
        <input type="radio"  name="gioitinh" value="Nữ" required>
        <label for="nu">Nữ</label><br><br>
        <label for="sothich">So thich: </label><br>
        <textarea name="sothich"></textarea><br><br>

        <label for="image">Hinh anh: </label>
        <input type="file" name="image" accept="image/*"><br><br>

        <label for="tinh">Tinh: </label>
        <select name="tinh" require>
            <option value="">Chon tinh</option>
            <option value="hanoi">Ha Noi</option>
            <option value="hochiminh">Ho Chi Minh</option>
            <option value="danang">Da Nang</option>
        </select><br><br>

        <input type="submit" value="Dang ky" >
        <input type="reset" value="Reset">
    </form>
    </fieldset>
</body>
</html>