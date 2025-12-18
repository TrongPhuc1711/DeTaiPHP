
<?php
$content = file_get_contents("http://thethao.vnexpress.net/");
if ($content !== false) {
    echo "DOC DUOC TRANG WEB";
    $pattern = '/<h3\s+class=["\']title-news["\'][^>]*>(.*?)<\/h3>/is';
    preg_match_all($pattern, $content, $arr);
    
    if(!empty($arr[0])){
        $i=0;
        ?>
        <table width =100% border=1 cellspacing=0 cellpadding=5>
            <tr>
                <td>STT</td>
                <td>NOI DUNG</td>
            </tr>
            <?php
                foreach ($arr[0] as $h3Content) {
                    echo "<tr><td>" .  $i++ . "</td><td>" . (htmlspecialchars($h3Content));
                }
            } else {
                echo "Không tìm thấy các thẻ <h3 class=\'title-news\'>";
            }
            echo "</td></table>";
        }
?>