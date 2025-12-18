<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=0, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        if(isset($_GET['id'])){
            $id=$_GET['id'];
            echo"Gia tri cua id la: $id";
        
        }else{
            echo "Khong tim thay tham so id trong form";
        }
    ?>
</body>
</html>