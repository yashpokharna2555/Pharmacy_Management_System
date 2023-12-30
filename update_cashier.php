<?php
session_start();
include_once('connect_db.php');

if(isset($_SESSION['username'])){
    $id = $_SESSION['admin_id'];
    $adminUsername = $_SESSION['username'];
} else {
    header("location:http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    exit();
}

$message1 = "";

if(isset($_POST['submit'])){
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $sid = $_POST['staff_id'];
    $postal = $_POST['postal_address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $pas = $_POST['password'];

    $sql = "UPDATE cashier SET first_name='$fname', last_name='$lname', staff_id='$sid', postal_address='$postal', phone='$phone', email='$email', username='$username', password='$pas' WHERE username='$adminUsername'";

    $result = mysqli_query($conn, $sql);

    if($result) {
        $message1 = "<font color=green>Update Successful</font>";

        // Redirect to admin_cashier.php
        header("location:http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/admin_cashier.php");
        exit();
    } else {
        $message1 = "<font color=red>Update Failed, Try again</font>";
    }
}

// Fetch the updated values for populating the form fields
$sqlFetchData = "SELECT * FROM cashier WHERE username='$adminUsername'";
$resultFetchData = mysqli_query($conn, $sqlFetchData);
$rowFetchData = mysqli_fetch_assoc($resultFetchData);

// Assign the fetched values to variables
$fname = $rowFetchData['first_name'];
$lname = $rowFetchData['last_name'];
$sid = $rowFetchData['staff_id'];
$postal = $rowFetchData['postal_address'];
$phone = $rowFetchData['phone'];
$email = $rowFetchData['email'];
$username = $rowFetchData['username'];
$pas = $rowFetchData['password'];
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $adminUsername;?> - Pharmacy Sys</title>
<link rel="stylesheet" type="text/css" href="style/mystyle.css">
<link rel="stylesheet" href="style/style.css" type="text/css" media="screen" /> 
<script src="js/function.js" type="text/javascript"></script>
<style>#left-column {height: 477px;}
 #main {height: 477px;}</style>
</head>
<body>
<div id="content">
<div id="header">
<h1><a href="#"><img src="images/hd_logo.jpg"></a> Pharmacy Sys</h1></div>
<div id="left_column">
<div id="button">
<ul>
    <li><a href="admin.php">Dashboard</a></li>
    <li><a href="admin_pharmacist.php">Pharmacist</a></li>
    <li><a href="admin_manager.php">Manager</a></li>
    <li><a href="admin_cashier.php">Cashier</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>	
</div>
</div>
<div id="main">
<div id="tabbed_box" class="tabbed_box">  
    <h4>Manage Users</h4> 
    <hr/>	
    <div class="tabbed_area">  
      
        <ul class="tabs">  
            <li><a href="javascript:tabSwitch('tab_1', 'content_1');" id="tab_1" class="active">Update User</a></li>  
              
        </ul>  
          
        <div id="content_1" class="content">  
            <?php echo $message1; ?>
            <form name="myform" onsubmit="return validateForm(this);" action="update_cashier.php" method="post">
                <table width="420" height="106" border="0">    
                    <tr><td align="center"><input name="first_name" type="text" style="width:170px" placeholder="First Name" value="<?php echo $fname; ?>" id="first_name" /></td></tr>
                    <tr><td align="center"><input name="last_name" type="text" style="width:170px" placeholder="Last Name" id="last_name" value="<?php echo $lname; ?>" /></td></tr>
                    <tr><td align="center"><input name="staff_id" type="text" style="width:170px" placeholder="Staff ID" id="staff_id" value="<?php echo $sid; ?>" /></td></tr>  
                    <tr><td align="center"><input name="postal_address" type="text" style="width:170px" placeholder="Address" id="postal_address" value="<?php echo $postal; ?>" /></td></tr>  
                    <tr><td align="center"><input name="phone" type="text" style="width:170px" placeholder="Phone" id="phone" value="<?php echo $phone; ?>" /></td></tr>  
                    <tr><td align="center"><input name="email" type="email" style="width:170px" placeholder="Email" id="email" value="<?php echo $email; ?>" /></td></tr>   
                    <tr><td align="center"><input name="username" type="text" style="width:170px" placeholder="Username" id="username" value="<?php echo $username; ?>" /></td></tr>
                    <tr><td align="center"><input name="password" placeholder="Password" id="password" value="<?php echo $pas; ?>" type="password" style="width:170px"/></td></tr>
                    <tr><td align="center"><input name="submit" type="submit" value="Update"/></td></tr>
                </table>
            </form>
        </div>  
    </div>  
</div>
</div>
<div id="footer" align="Center"> CodeWithYash - Pharmacy Management System</div>
</div>
</body>
</html>
