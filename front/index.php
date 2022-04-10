<html>
<head>
  <title>Debug page</title>
</head>
<body style="background-color: #FFFFFF;">
  <?php
    $requestPageUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $pubIp = file_get_contents('https://api.ipify.org');
  ?>
  <h3>Command</h3>
  <form action="process.php" method="post">
Enter command: <input type="text" name="name">
<input type="submit">
</form>
<h3>Debug information</h3>
<br><b>Request Url: </b><?php echo $requestPageUrl; ?>
<br><b>Outbound public IP: </b><?php echo $pubIp ?>
<br><b>Inbound client IP: </b><?php echo $_SERVER['REMOTE_ADDR']; ?>
<br><b>Hostname: </b><?php echo gethostname(); ?>
<br><b>OS: </b><?php echo php_uname(); ?>
<h3>Environment variables</h3>
 <br>TEST<br><br>
<br>
  <br><?php
    $output=null;
    $retval=null;
    exec("/bin/bash -c printenv", $output, $retval);
    foreach ($output as $line) {
        echo "<br>$line";
    }
?>
<h3>Headers</h3>
    <?php
      foreach (getallheaders() as $name => $value) {
        if ($name == 'X-MS-TOKEN-AAD-ACCESS-TOKEN') {
          $access_token=$value;
        }
        echo "<br><b>$name: </b>$value";
      }
    ?>

<h3>TOKEN</h3>

<?php
  echo "$access_token";
?>

<h3>API</h3>
<?php
$url = "https://appservice-demo04-backend.azurewebsites.net";
$ch = curl_init($url);
$request_headers = array();
$request_headers[] = 'Accept: application/json';
$request_headers[] = 'Authorization: Bearer '. $access_token; 
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo "<br>Result<br><br>".$result;
$json = json_decode($result, true);
?>

<h3>Graph Access Token</h3>
<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/3f22735f-578d-4544-a1dd-f60506f832cb/oauth2/token");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer"
    ."&client_id=".urlencode("f93dc346-f757-4a12-b20a-40e778e6e731")
    ."&client_secret=".urlencode("b8m7Q~R4S3V.qPqzN2HP8s4JEEudesPEhDzgB")
    ."&assertion=".urlencode($access_token)
    ."&requested_token_use=on_behalf_of"
    ."&scope=".urlencode("openid profile email"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec ($ch);
curl_close ($ch); 
$jsonoutput = json_decode($server_output, true);
$bearertoken = $jsonoutput['access_token'];
echo "<br>".$bearertoken
?>

<h3>GRAPH</h3>
<?php
$url = "https://graph.microsoft.com/oidc/userinfo";
$ch = curl_init($url);
$request_headers = array();
$request_headers[] = 'Accept: application/json';
$request_headers[] = 'Authorization: Bearer '. $bearertoken; 
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo "<br>".$result;
$json = json_decode($result, true);
?>
</body>
</html>
