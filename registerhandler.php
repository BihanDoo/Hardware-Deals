<?php
    $name = $_POST["name"];
    $email = $_POST["userName"];
    $password = $_POST["password"];
    $contactNumber = $_POST["contact"];
    $address = $_POST["address"];

    $con = mysqli_connect("localhost","root","","hardwaredeals");
    if(!$con){
        die("sorry, technical issue");

    }
    $sql = "INSERT INTO `users` (`uEmail`, `name`, `password`, `address`, `contact`) VALUES ('".$email."', '".$name."', '".$password."', '".$address."', '".$contactNumber."');";

    mysqli_query($con,$sql);
    mysqli_close($con);
    header('Location:login.html');
    exit;
?>