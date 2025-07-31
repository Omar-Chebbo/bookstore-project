<?php
        session_start();
        session_unset();
        session_destroy();

        header("location:https://bookstore.kesug.com/I439-Project");
?>
