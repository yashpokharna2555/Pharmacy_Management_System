<?php
include('connect_db.php');
session_start();
if(isset($_POST['customer_id']))
{
$c_id=$_POST['customer_id'];
	$cname=$_POST['customer_name'];
	$age=$_POST['age'];
	$sex=$_POST['sex'];
	$postal=$_POST['postal_address'];
	$phone=$_POST['phone'];
	
	$_SESSION['custId']=$c_id;
	$_SESSION['custName']=$cname;
	$_SESSION['age']=$age;
	$_SESSION['sex']=$sex;
	$_SESSION['postal_address']=$postal;
	$_SESSION['phone']=$phone;
						
}


if (isset($_POST['drug_name'])) {
    $drug = $_POST['drug_name'];
    $strength = $_POST['strength'];
    $dose = $_POST['dose'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO tempPrescri(customer_id, customer_name, age, sex, postal_address, phone, drug_name, strength, dose, quantity)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssssssss", $_SESSION['custId'], $_SESSION['custName'], $_SESSION['age'], $_SESSION['sex'], $_SESSION['address'], $_SESSION['phone'], $drug, $strength, $dose, $quantity);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['quantity'] = $quantity;

    $get_cost_query = "SELECT cost FROM stock WHERE drug_name = ?";
    $get_cost_stmt = mysqli_prepare($conn, $get_cost_query);
    mysqli_stmt_bind_param($get_cost_stmt, "s", $drug);
    mysqli_stmt_execute($get_cost_stmt);
    mysqli_stmt_bind_result($get_cost_stmt, $cost);

    // Fetch the result
    mysqli_stmt_fetch($get_cost_stmt);
    // Now $cost contains the cost value
    mysqli_stmt_close($get_cost_stmt);

    $tot = $quantity * $cost;

    $file = fopen("receipts/docs/" . $_SESSION['custId'] . ".txt", "a+");
    fwrite($file, $drug . ";" . $strength . ";" . $dose . ";" . $quantity . ";" . $cost . ";" . $tot . "\n");
    fclose($file);

    echo "<table width=\"100%\" border=1>";
    echo "<tr>
            <th>Drug</th>
            <th>Strength </th>
            <th>Dose</th>
            <th>Quantity </th>
          </tr>";
    
    // Loop through results of database query, displaying them in the table
    $result = mysqli_query($conn, "SELECT * FROM tempPrescri") or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        // Echo out the contents of each row into a table
        echo "<tr>";
        echo '<td>' . $row['drug_name'] . '</td>';
        echo '<td>' . $row['strength'] . '</td>';
        echo '<td>' . $row['dose'] . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo "</tr>";
    }
}

?>

<?php
if (isset($_POST['invoice_no'])) {
    $invoice = $_POST['invoice_no'];
    $receipt = $_POST['receipt_no'];
    $amount = $_POST['amount'];
    $payment = $_POST['payment_type'];
    $serial = $_POST['serial_no'];

    $_SESSION['receiptNo'] = $receipt;
    $_SESSION['amount'] = $amount;
    $_SESSION['paymentType'] = $payment;
    $_SESSION['serialNo'] = $serial;

    // Assuming you have a MySQLi connection established earlier
    $mysqli = new mysqli("localhost", "root", "", "pharmacy");

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $getDetails = $mysqli->query("SELECT invoice, drug, cost, quantity FROM invoice_details WHERE invoice='{$invoice}'");

    $file = fopen("receipts/docs/" . $_SESSION['invoiceNo'] . ".txt", "w");

    echo "<table width=\"100%\" border=1>";
    echo "<tr> 
        <th>Drug </th>
        <th>Unit cost</th>
        <th>Quantity </th>
        <th>Total Cost(Ksh.)</th>
    </tr>";

    while ($item5 = $getDetails->fetch_assoc()) {
        $getDrug = $mysqli->query("SELECT drug_name FROM stock WHERE stock_id='{$item5['drug']}'");
        $drug = $getDrug->fetch_assoc();

        // I assume that you want to get the quantity from the invoice_details table, not from a separate query
        $qtty = $item5['quantity'];
        $tot = $item5['cost'] * $qtty;
        $total[] = $tot;
        fwrite($file, $drug['drug_name'] . ";" . $item5['cost'] . ";" . $qtty . ";" . $tot . ";\n");

        echo "<tr>";
        echo '<td>' . $drug['drug_name'] . '</td>';
        echo '<td align="right">' . number_format($item5['cost'], 2) . '</td>';
        echo '<td align="right">' . $qtty . '</td>';
        echo '<td align="right">' . number_format($tot, 2) . '</td>';
        echo "</tr>";
    }

    if (is_array($total) && !empty($total)) {
        // If it's an array with elements, calculate the sum
        $zote = array_sum($total);
    } else {
        // If it's not a valid array, handle it differently (e.g., set a default value or display an error message)
        $zote = 0; // Example using a default value of 0
        // Or:
        echo "Error: $total is not a valid array for array_sum()."; // Example error message
    }
    
    echo "<tr>";
    echo '<td><strong>TOTAL</strong></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td align="right">' . number_format($zote, 2) . '</td>';
    echo "</tr>";

    fwrite($file, "TOTAL;;;".$zote.";\n");
    fclose($file);
    echo "</table>";

    // Close the MySQLi connection
    $mysqli->close();
}
?>

