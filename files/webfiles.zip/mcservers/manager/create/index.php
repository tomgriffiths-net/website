<?php
require '../../../../localfiles/global.php';

$types = array(
    "vanilla"   => "vanilla/",
    "paper"     => "papermc/?type=paper",
    "velocity"  => "papermc/?type=velocity",
    "waterfall" => "papermc/?type=waterfall",
    "forge"     => "forge/"
);

if(isset($_POST['servertype'])){
    html::loadurl($types[$_POST['servertype']]);
}

html::fullhead("mcservers");

echo '
    <form id="form1" method="post" style="max-width:300px;">
        <h4>Select server version:<br></h4>
        <select name="servertype" class="form-select">
            ';
            foreach($types as $type => $url){
                $name = ucfirst($type);
                echo '<option value="' . $type . '">' . $name . '</option>';
            }
            echo '
        </select>
        <br>
        <button class="btn btn-success" onclick="this.innerHTML=\'Loading...\'" type="submit" name="submit">Next</button>
    </form>
';

html::fullend();