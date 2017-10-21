<?php
session_start();
require 'vendor/autoload.php';

use  Aws\Rds\RdsClient;


$client = RdsClient::factory(array(
        'version' => 'latest',
        'region' => 'us-west-2'
));
$s3 = new Aws\S3\S3Client(['version' => 'latest', 'region' => 'us-west-2']);
$result = $client->describeDBInstances(array(
        'DBInstanceIdentifier' => 'itmo544-pboov',
));
$endpoint = "";
$url = "";

foreach($result['DBInstances'] as $ep)
        {

        // echo $ep['DBInstanceIdentifier'] . "<br />";

        foreach($ep['Endpoint'] as $endpointurl)
                {
                $url = $endpointurl;
                break;
                }
        }

$conn = mysqli_connect($url, "pboo", "pboopass", "miniproj", "3306") or die("Error " . mysqli_error($link));
$name = $_FILES["fileToUpload"]["name"];
$tmp = $_FILES['fileToUpload']['tmp_name'];
$resultput = $s3->putObject(array(
        'Bucket' => 'pboov-color',
        'Key' => $name,
        'SourceFile' => $tmp,
        'region' => 'us-west-2',
        'ACL' => 'public-read'
));
$imageurl = $resultput['ObjectURL'];
$_SESSION['s3-raw'] = $imageurl;


$rawurl = $imageurl;
$im = ''; // replace this path with $rawurl
$checkimgformat = substr($rawurl, -3);

if ($checkimgformat == 'png' || $checkimgformat == 'PNG')
        {
        $im = imagecreatefrompng($rawurl);
        }
  else
        {
        $im = imagecreatefromjpeg($rawurl);
        }

$lstoccuranceofslash = strripos($rawurl, "/") + 1;
$imagename = substr($rawurl, $lstoccuranceofslash, strlen($rawurl));

ImageFilter($im, IMG_FILTER_GRAYSCALE);
$tmp = "/tmp/$imagename";

// output and free memory
// header('Content-type: image/png');

imagepng($im, $tmp);
imagedestroy($im);

// $tmp="/tmp/$imagename";

$resultfinalput = $s3->putObject(array(
        'Bucket' => 'pboov-bw',
        'Key' => $imagename,
        'SourceFile' => $tmp,
        'region' => 'us-west-2',
        'ACL' => 'public-read'
));
$finishedimageurl = $resultfinalput['ObjectURL'];
/**/

if (!($stmt2 = $conn->prepare("INSERT INTO records (id,email,phone,s3_raw_url,s3_finished_url,status,receipt) VALUES (NULL,?, ?, ?, ?, ?, ?)")))
        {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

$stmt = $conn->prepare("INSERT INTO records (email,phone,s3_raw_url,s3_finished_url,status,receipt) VALUES (?, ?, ?, ?, ?, ?)");
$statusnumber = 1;
$stmt->bind_param("ssssss", $email, $phone, $s3_raw_url, $s3_finished_url, $status, $receipt);
$email = $_SESSION['emailid'];
$phone = "6036744303";
$s3_raw_url = $imageurl;
$s3_finished_url = $finishedimageurl;
$status = $statusnumber;
$receipt = md5($imageurl);
$stmt->execute();
$stmt->close();
$conn->close();
$_SESSION['receipt'] = $receipt;

?>

<html>
<head>
<title>Uploaded Image</title>
<style>
body {
    margin: 0;
}
ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 25%;
    background-color: #f1f1f1;
    position: fixed;
    height: 100%;
    overflow: auto;
}
li a {
    display: block;
    color: #000;
    padding: 8px 16px;
    text-decoration: none;
    border-bottom: 1px solid #555;
}
li a.active {
    background-color: #4CAF50;
    color: white;
}
li a:hover:not(.active) {
    background-color: #555;
    color: white;
}
.button {
    background-color: #4CAF50;
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
}
.buttonreturn{
    background-color: #4CAF50;
    color: white;
    padding: 14px 25px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymo
CloudMini  gallery.php  up.php  upr.php  welcome.php
us">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>

<nav class="navbar navbar-inverse bg-inverse">
<a class="navbar-brand" href="welcome.php">Photo App</a>
<a class="navbar-brand" href="/gallery.php">Gallery<span class="sr-only">(current)</span></a>
<a class="navbar-brand" href="/up.php">Upload</a>  
    </nav>

<div style="margin-left:25%;padding:1px 16px;height:1000px;">

<form action="" method='post' enctype="multipart/form-data">
<h1>Image Successfully Uploaded</h1>
<h3>
<img src="<?php
echo $imageurl; ?>" height="200" width="200">
<br />
<br />

</form>
</div>
</body>
</html>
