<?php
require '../../../../localfiles/global.php';

html::fullhead("main", "Navbars list");

$headerData = website_json::readFile($localDir . "\\headers\\" . $id . ".json");

foreach(glob($localDir . "/headers/*.json") as $header){
    $header = basename($header);
    $header = substr($header,0,strripos($header,"."));
    echo '<a class="link" href="../edit/?id=' . $header . '">' . $header . '</a><br>';
}

html::fullend();