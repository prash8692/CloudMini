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
        'DBInstanceIdentifier' => 'itmo544-krose1-mysqldb',
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

$conn = mysqli_connect($url, "controller", "controllerpass", "school", "3306") or die("Error " . mysqli_error($link));
$name = $_FILES["fileToUpload"]["name"];
$tmp = $_FILES['fileToUpload']['tmp_name'];
$resultput = $s3->putObject(array(
        'Bucket' => 'raw-kro',
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
echo "finding the last position of the slash symbol:     " . $lstoccuranceofslash . "\n";
$imagename = substr($rawurl, $lstoccuranceofslash, strlen($rawurl));
echo $imagename . "\n";

ImageFilter($im, IMG_FILTER_GRAYSCALE);
$tmp = "/tmp/$imagename";
echo "the tmp directory" . $tmp . "\n";

// output and free memory
// header('Content-type: image/png');

imagepng($im, $tmp);
imagedestroy($im);
echo shell_exec('ls -ltr /tmp') . "\n";

// $tmp="/tmp/$imagename";

$resultfinalput = $s3->putObject(array(
        'Bucket' => 'finish-kro',
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
</head>
<body>

<ul>
  <li><a href="/welcome.php">Home</a></li>
  <li><a href="/gallery.php">Gallery</a></li>
  <li><a href="/up.php">upload</a></li>
</ul>

<div style="margin-left:25%;padding:1px 16px;height:1000px;">

<form action="" method='post' enctype="multipart/form-data">
<h1>Success!</h1>
<br />
<br />
<h3>Name of the image: <?php
echo $name; ?><h3>
<img src="<?php
echo $imageurl; ?>" height="200" width="200">
<br />
<br />

</form>
</div>
</body>
</html>
