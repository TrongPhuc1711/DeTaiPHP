<?php
    $a=0;
    $b=2;
    $c=4;
    echo "Giai phuong trinh bac 2: $a x^2 + $b x + $c<br>";
    if($a==0){
        if($b==0){
           if($c==0){
                echo "PT vo so nghiem";
            }
            else echo "PT vo nghiem";
        }
        else{
            $x=-$c/$b;
            echo " PT co 1 nghiem: X= $x";
        }
    }else{
        $delta=($b*$b)-(4*$a*$c);
        echo "delta= $delta";
        if($delta<0){
            echo "<br>PT vo nghiem";
        }
        elseif($delta==0){
            $x=-$b/(2*$a);
            echo "PT co nghiem kep: x1 = x2 = $x";
        }else{
            $x1=(-$b+sqrt($delta))/(2*$a);
            $x1=(-$b-sqrt($delta))/(2*$a);
            echo "PT co 2 nghiem phan biet: ";
            echo "x1= $x1";
            echo "x2= $x2";
        }
    }
?>