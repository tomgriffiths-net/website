<?php
require '../../../../localfiles/global.php';

$id = false;
if(isset($_GET['id'])){
    if(preg_match("/^[a-zA-Z0-9-_.]+$/", $_GET['id'])){
        if(is_file($localDir . "\\headers\\" . $_GET['id'] . ".json")){
            $id = $_GET["id"];
        }
    }
}

if(!is_string($id)){
    html::loadurl('../list/');
}


html::head();
echo '
    .btn-group-vertical .btn{
        line-height: 1;
        height: 18.5px;
        width: 30px;
    }
';
html::top("main", "Header config");

$data = html::getNavButtons($id);
if(!is_array($data)){
    html::loadurl('../list/');
}


echo '
    <div id="cards" class="container py-4"></div>
    <script>
        const id="'.$id.'";

        function doAction(action, iden=false){
            const div = document.getElementById("cards");

            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                div.innerHTML = this.responseText;
            }
            xhttp.open("GET", "action.php?action=" + action + "&id=" + id + (iden ? "&iden=" + iden : ""));
            xhttp.send();
        }

        function saveTextDirect(iden, text, isLink=false){
            const xhttp = new XMLHttpRequest();
            xhttp.open("GET", "action.php?action=saveText&id=" + id + "&iden=" + iden + "&value=" + encodeInString(text) + (isLink ? "&isLink=1" : ""));
            xhttp.send();
        }

        const saveText = debounceBasedOnFirstParameter(saveTextDirect, 500);

        doAction("getCards");
    </script>
';


html::fullend();