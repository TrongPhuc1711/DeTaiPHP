<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form thong tin</form></title>
</head>
<body>
    <fieldset>
        <legend>Form5_4_2</legend>
        <form action="formThongTin.php" method="get">
            Nhap ten san pham can tim: <input type="text" name="ten" require><br>
            Cach tim: <input type="radio" name="ct" value="Gan_dung">Gan dung
                <input type="radio" name="ct" value="Chinh_xac">Chinh xac <br>
            Loai san pham: <br>
                    <input type="checkbox" name="loai[]" value="loai1">Loai 1 <br>
                    <input type="checkbox" name="loai[]" value="loai2">Loai 2 <br>
                    <input type="checkbox" name="loai[]" value="loai3">Loai 3 <br>
                    <input type="checkbox" name="loai[]" value="tatca">Tat ca <br>
            <input type="submit" onclick="chekAll();">
        </form>
    </fieldset>
    
    <?php 
        if(isset($_GET['ten'])){
            echo"ten san pham: " .htmlspecialchars($_GET['ten']);
            echo "</br>";
        }
        if(isset($_GET['ct'])){
            echo "Cach tim: ".htmlspecialchars($_GET['ct']);
            echo "</br>";        
        }
        if(isset($_GET['loai'])){
            echo "Loai san pham: ";
            if(is_array(($_GET['loai']))){
                echo implode(", ",$_GET['loai']);
                //implode: Nối các giá trị trong mảng loai[] thành một chuỗi, ngăn cách nhau bằng dấu phẩy.
            }
        }else{
            echo" chua chon loai";
        }
        echo "<hr>";
        print_r($_GET);
    ?>
</body>
</html>