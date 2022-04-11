<?php

      foreach(getallheaders() as$name=> $value) {
        if($name== 'Authorization') {
          $access_token=$value;
          echo"<br><b>$name: </b>$access_token";
        }

      }
header('Content-type: application/json');
echo '{ "server": "' . gethostname() .'" }'; 
?>
