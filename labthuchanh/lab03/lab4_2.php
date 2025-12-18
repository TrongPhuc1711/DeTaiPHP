<?php
    function nNhoNhat(){
        $i=1;
        $sum=0;
        while($sum<1000){
            $sum+=$i;
            $i++;
        
        }
        return $i;
    }
    $timMin=nNhoNhat();
    echo "n nho nhat tong > 1000: $timMin ";
?>