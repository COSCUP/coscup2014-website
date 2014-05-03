<?php

function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
}

if (!endswith($_FILES['file']['name'], '.png')) {
    $_FILES['file']['name'] = $_FILES['file']['name'] . '.png';
}

move_uploaded_file($_FILES['file']['tmp_name'], './files/' . $_FILES['file']['name']);

?>
