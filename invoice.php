<?php
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
include('connect_db.php');
session_start();
$c_id = $_SESSION['custId'];
$cname = $_SESSION['custName'];
$age = $_SESSION['age'];
$sex = $_SESSION['sex'];
$postal = $_SESSION['postal_address'];
$phone = $_SESSION['phone'];
$quantity = $_SESSION['quantity'];
$t = time();
$user = $_SESSION['username'];
$time = date("l\, F d Y\, h:i:s A", $t);
$invoiceNo = $_SESSION['invoice'];

// Create a connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the maximum prescription_id
$query = "SELECT 1+MAX(prescription_id) as max_prescription_id FROM prescription";
$result = $conn->query($query);

// Check for errors
if (!$result) {
    die("Error: " . $conn->error);
}

// Fetch the result
$row = $result->fetch_assoc();
$presId = $row['max_prescription_id'];

// If the value is empty, set a default value
$presIdd = ($presId == '') ? 999 : $presId;

// Insert into the prescription table
$sqlP = $conn->query("INSERT INTO prescription(prescription_id, customer_id, customer_name, age, sex, postal_address, invoice_id, phone)
VALUES('{$presIdd}', '{$c_id}', '{$cname}', '{$age}', '{$sex}', '{$postal}', '{$invoiceNo}', '{$phone}')");

// Insert into the invoice table
$sqlI = $conn->query("INSERT INTO invoice(invoice_id, customer_name, served_by, status)
VALUES('{$invoiceNo}', '{$cname}', '{$user}', 'Pending')");

// Select from tempprescri using prepared statement to prevent SQL injection
$getDetailsStmt = $conn->prepare("SELECT stock_id, cost FROM stock WHERE drug_name = ?");
$getDetailsStmt->bind_param("s", $drug_name);

// Continue your processing...
if ($getDetailsResult) {
    // Proceed with the loop only if $getDetailsResult is not null
    while ($item1 = $getDetailsResult->fetch_assoc()) {
		$getDetailsStmt->execute();
		$getDetailsResult = $getDetailsStmt->get_result();
	
		// Continue your processing...
		while ($row = $getDetailsResult->fetch_assoc()) {
			// Process each row as needed
			// $row['stock_id'] and $row['cost'] are the columns from the query
	
			// Insert into invoice_details
			$sqlId = $conn->query("INSERT INTO invoice_details(invoice, drug, cost, quantity)
			VALUES('{$invoiceNo}','{$row['stock_id']}','{$row['cost']}','{$item1['quantity']}')");
	
			$count[] = $row['cost'] * $item1['quantity'];
		}
    }
} else {
    // Handle the case where the query failed
    echo "Query failed: " . mysqli_error($conn); // Assuming mysqli connection
}


// Close the prepared statement
// $getDetailsStmt->close();

// Close the connection
// $conn->close();

if (is_array($count) && !empty($count)) {
    $tot = array_sum($count);
} else {
    // Handle the case where $count is not an array or is empty
    echo "Error: $count is not a valid array.";
}
			$file=fopen("receipts/docs/".$c_id.".txt", "a+");
	fwrite($file, "TOTAL;;;;;".$tot."\n");
	fclose($file);
	$connection = mysqli_connect("localhost", "root", "", "pharmacy");
	if (!$connection) {
		die("Connection failed: " . mysqli_connect_error());
	}
	
	$getDetails = mysqli_query($connection, "SELECT * FROM tempprescri WHERE customer_id='{$c_id}'");
	while ($item12 = mysqli_fetch_array($getDetails))
			{	
				$getDetails12Stmt = $conn->prepare("SELECT stock_id, cost FROM stock WHERE drug_name = ?");
				$getDetails12Stmt->bind_param("s", $item12['drug_name']);
				$getDetails12Stmt->execute();
				$getDetails12Result = $getDetails12Stmt->get_result();

// Fetch details using fetch_assoc
$details2 = $getDetails12Result->fetch_assoc();

// Close the statement
$getDetails12Stmt->close();
$sqlIpStmt = $conn->prepare("INSERT INTO prescription_details(pres_id, drug_name, strength, dose, quantity)
    VALUES(?, ?, ?, ?, ?)");
$sqlIpStmt->bind_param("sssss", $presIdd, $details2['stock_id'], $item12['strength'], $item12['dose'], $item12['quantity']);
$sqlIpStmt->execute();
$sqlIpStmt->close();

			}
			$sqlDStmt = $conn->prepare("DELETE FROM tempprescri WHERE customer_id = ?");
			$sqlDStmt->bind_param("s", $c_id);
			$sqlDStmt->execute();
			$sqlDStmt->close();
			
				
	
	//select all from temp prescri where cust/Id =$c_id
	 
class MYPDF extends TCPDF {

	// Load table data from file
	public function LoadData($file) {
		// Read file lines
		$lines = file($file);
		$data = array();
		foreach($lines as $line) {
			$data[] = explode(';', chop($line));
		}
		return $data;
	}

	// Colored table
	public function ColoredTable($header,$data) {
		// Colors, line width and bold font
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(0);
		$this->SetDrawColor(255, 255,255);
		$this->SetLineWidth(0);
		$this->SetFont('', 'B');
		// Header
		$w = array(30,15,9,15,15,15);
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			$this->Cell($w[$i], 5, $header[$i], 1, 0, 'L', 1);
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(0);
		$this->SetFont('');
		// Data
		$fill = 0;
		foreach($data as $row) {
			$this->Cell($w[0], 4, $row[0], 'LR', 0, 'L', $fill);
					
			$this->Cell($w[1], 4, $row[1], 'LR', 0, 'L', $fill);
			$this->Cell($w[2], 4, $row[2], 'LR', 0, 'L', $fill);
			$this->Cell($w[3], 4, $row[3], 'LR', 0, 'R', $fill);
			if (is_numeric($row[4])) {
				// If it's a number, format it
				$this->Cell($w[4], 4, number_format($row[4], 2), 'LR', 0, 'R', $fill);
			} else {
				// If it's not a number, handle it differently (e.g., display a default value or placeholder)
				$this->Cell($w[4], 4, 'N/A', 'LR', 0, 'R', $fill); // Example using "N/A"
			}
			
			if (is_numeric($row[5])) {
				$this->Cell($w[5], 4, number_format($row[5], 2), 'LR', 0, 'R', $fill);
			} else {
				$this->Cell($w[5], 4, 'N/A', 'LR', 0, 'R', $fill); // Example using "N/A"
			}
			
			$this->Ln();
			$fill=!$fill;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
	}
	
}


$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_INVOICE_FORMAT, true, 'UTF-8', false);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Pharm Syst');
$pdf->SetTitle('Invoice');
$pdf->SetSubject('Payment');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

//$pdf->SetHeaderData(PDF_RECEIPT_LOGO, PDF_RECEIPT_LOGO_WIDTH, PDF_RECEIPT_TITLE, PDF_HEADER_STRING, array(0,0,0), array(0,0,0));
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_RECEIPT, '', PDF_FONT_SIZE_RECEIPT));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
define('PDF_FONT_MONOSPACED', 'courier'); // Or any desired monospaced font



$pdf->SetMargins(PDF_INVOICE_LEFT, PDF_INVOICE_TOP, PDF_INVOICE_RIGHT);
$pdf->SetHeaderMargin(PDF_INVOICE_HEADER);
$pdf->SetFooterMargin(PDF_INVOICE_FOOTER);


$pdf->SetAutoPageBreak(TRUE, PDF_INVOICE_BOTTOM);


$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


$pdf->setLanguageArray($l);


$pdf->SetFont('times', '', 10, '', true);


$pdf->AddPage();
$spacing = -0.01;
$stretching = 75;
$pdf->setFontStretching($stretching);
				$pdf->setFontSpacing($spacing);
$titling= <<<EOD
<strong> <font style="font-size:11">Pharmacy Sys</font> </strong> <br>
Student Center Ground Floor,<br> P.O. Box Private Bag Kabarak, Kenya <br> Tel: +254 702 937 925 <br> E-mail: pharmacysys@yahoo.com <br>-----------------------------------------
EOD;
$header = array('Drug','Strength', 'Dose' ,'Quantity','Price', 'Total');
$ddt=<<<EOD
<strong>INVOICE N<sup>o</sup>:</strong> $invoiceNo  <br>
$time   
EOD;
$html = <<<EOD
<strong>Name: </strong> $cname <strong>ID N<sup>o</sup>: $c_id
<br>-----------------------------------------
EOD;


$data = $pdf->LoadData('receipts/docs/'.$c_id.'.txt');
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $titling, $border=0, $ln=1, $fill=0, $reseth=true, $align='C', $autopadding=true);
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $ddt, $border=0, $ln=1, $fill=0, $reseth=true, $align='L', $autopadding=true);
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

$pdf->ColoredTable($header, $data);

$pdf->SetY(-10);
		
		$pdf->Cell(0, 0, 'You were served by: '.strtoupper($user), 0, false, 'L', 0, '', 0, false, 'T', 'M');

$pdf->Output('receipts/printouts/'.$cname.'-invoice.pdf','F');
//unlink('receipts/docs/'.$c_id.'.txt');
unset($_SESSION['custId'], $_SESSION['custName'], $_SESSION['age'], $_SESSION['sex'], $_SESSION['postal_address'], $_SESSION['phone']);
header('Location: prescription.php');
exit;

?>