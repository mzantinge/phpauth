<?php
header('Content-type: application/json');
echo '{ "server": "' . gethostname() .'" }'; 
?>