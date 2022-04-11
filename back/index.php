<?php

      foreach(getallheaders() as$name=> $value) {
        if($name== 'Authorization') {
          $access_token=$value;
              
              print_r(json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $access_token)[1])))));
           
                   
        }
      }
header('Content-type: application/json');
echo '{ "server": "' . gethostname() .'" }'; 
?>
