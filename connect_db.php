<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
$conn = mysqli_connect('localhost', 'root', '', 'pharmacy');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>