<?php
session_start();

require 'vendor/autoload.php';

use Aws\Rds\RdsClient;

//include 'checkuploadenabled.php';

//$variable=returnenabledstatus();

$client = RdsClient::factory(array(
'version' => 'latest',
'region'  => 'us-west-2'
));


$result = $client->describeDBInstances(array(
    'DBInstanceIdentifier' => 'itmo544-pboov',
));


$endpoint = "";
$url="";

foreach ($result['DBInstances'] as $ep)
{
   // echo $ep['DBInstanceIdentifier'] . "<br>";

    foreach($ep['Endpoint'] as $endpointurl)
        {
        $url=$endpointurl;
                break;
        }
}


$link = mysqli_connect($url,"pboo","pboopass","miniproj","3306") or die("Error " . mysqli_error($link));


$sqlselect = "SELECT s3_raw_url,s3_finished_url FROM records where status=1";
$resultforselect = $link->query($sqlselect);


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
#lightbox {
    position:fixed; /* keeps the lightbox window in the current viewport */
    top:0; 
    left:0; 
    width:100%; 
    height:100%; 
    background:url(overlay.png) repeat; 
    text-align:center;
}
#lightbox p {
    text-align:right; 
    color:#fff; 
    margin-right:20px; 
    font-size:12px; 
}
#lightbox img {
    box-shadow:0 0 25px #111;
    -webkit-box-shadow:0 0 25px #111;
    -moz-box-shadow:0 0 25px #111;
    max-width:940px;
}
</style>
</head>
<body>

<ul>
  <li><a href="/welcome.php">Home</a></li>
  <li><a class="active" href="/gallery.php">Gallery</a></li>
  
</ul>

<div style="margin-left:25%;padding:1px 16px;height:1000px;">
<br>
<br>
<br>
<?php
if ($resultforselect->num_rows > 0) {
    // output data of each row
    while($row = $resultforselect->fetch_assoc()) {
		$value=$row["s3_raw_url"];
        echo "<a href='$value' class=\"lightbox_trigger\">";

        echo "<img src='$value' height=\"200\" width=\"200\" style=\"margin:0px 20px\" />";

        $valuefinish=$row["s3_finished_url"];
        echo "<a href='$valuefinish' class=\"lightbox_trigger\">";

        echo "<img src='$valuefinish' height=\"200\" width=\"200\"/>";
        echo"<br>";
        echo"<hr>";
    }
} else {
    echo "0 results";
}
$link->close();
?>
</div>
</body>
</html>

