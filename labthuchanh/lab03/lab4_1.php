<?php
    function tongChan(){
        $sum=0;
        for($i=2;$i<=100;$i+=2){
            $sum+=$i;
        }
        return $sum;
    }
    $tongchan=tongChan();
    echo "Tong chan tu 2 den 100 la: $tongchan";
?>