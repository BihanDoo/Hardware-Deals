<?php 
session_start();
    if(isset($_POST["btnSubmit"]))
    {
        $userName = $_POST["userName"];
        $password = $_POST["password"];

        $con = mysqli_connect("localhost", "root", "", "hardwaredeals");
        if(!$con)
        {
            die("Sorry we are facing an issue right now, Will get back to you later :) ");
        }
        $sql = "SELECT * FROM `users` WHERE `uEmail`='".$userName."' AND `password`='".$password."'";
        {
            $results = mysqli_query($con,$sql);
            if(mysqli_num_rows($results)>0)
            {
                $_SESSION["userName"] = $userName;
                header('Location:index.php');
            }
            else
            {
                
                header('Location:login.html');
            }
        }
    }
?>