<?php
require_once 'function.php';

if(isset($_POST['user'])){
    $user=sanitizeString($_POST['user']);
    $result=queryMysql($pdo,"SELECT * FROM members WHERE user='$user'");
    
    if($result->rowCount())
        echo "<span class='taken'>&nbsp;&#x2718; " .
            "The username '$user' is taken</span>";
    else
     echo "<span class='available'>&nbsp;&#x2714; " .
            "The username '$user' is available</span>";
}
require_once 'footer.php';
?>