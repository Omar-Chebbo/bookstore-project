<?php require "../config/config.php"; ?>


<?php
session_start();


    if(isset($_SESSION['user_id'])){
        $product_id = $_POST["product_id"];
        $user_id = $_SESSION["user_id"];
        $content = $_POST["content"];

        $insert=$conn->prepare("INSERT INTO comments(user_id,product_id,content)
            VALUES(:user_id, :product_id, :content)");

            $insert->execute([
                ':user_id'=>$user_id,
                ':product_id'=>$product_id,
                ':content'=>$content
            ]);

            
            echo  $content."%%seperator%%".$_SESSION["username"];
        exit;
        
    }

?>