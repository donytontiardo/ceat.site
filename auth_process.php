<?php
session_start();
include 'database.php';

if (isset($_POST['regis'])) {
    if ($_POST['regis']) {
        $name = $_POST['name'];
        $age = $_POST['age'];
        $nim = $_POST['nim'];
        $password = $_POST['password'];

        $query = "SELECT * FROM user WHERE nim=$nim";
        $results = $mysqli->query($query);

        if ($results->num_rows > 0) {
            echo "<script>window.alert('NIM Sudah Terdaftar');
                window.location.href='regis.php';</script>";
        } else {
           
            $query = "INSERT INTO user (name,age,nim,password) VALUES ('$name','$age','$nim','$password')";
            $results = $mysqli->query($query);
            echo "<script>window.location.href='index.php';</script>";
        }
    }
}

if (isset($_POST['login'])) {
    if ($_POST['login']) {
        $nim = $_POST['nim'];
        $password = $_POST['password'];
        $query = "SELECT * FROM user WHERE nim='$nim'";
        $results = $mysqli->query($query);
        if ($results->num_rows > 0) {
            $user = mysqli_fetch_assoc($results);
            if ($user['password'] == $password) {
                $_SESSION['usertest'] = "true";
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['nim'] = $user['nim'];
                echo "<script>window.location.href='index.php';</script>";
            } else {
                echo "<script>window.alert('Password Salah');
            window.location.href='login.php';</script>";
            }
        } else {
            echo "<script>window.alert('NIM Tidak Terdaftar');
            window.location.href='login.php';</script>";
        }
    }
}

if (isset($_POST['logout'])) {
    if ($_POST['logout']) {
        session_destroy();
        echo "<script>window.location.href='index.php';</script>";
    }
}
