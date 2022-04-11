<?php

      foreach(getallheaders() as$name=> $value) {
        if($name== 'X-MS-TOKEN-AAD-ACCESS-TOKEN') {
          $access_token=$value;
        }
        echo"<br><b>$name: </b>$value";

header('Content-type: application/json');
echo '{ "server": "' . gethostname() .'" }'; 
?>
