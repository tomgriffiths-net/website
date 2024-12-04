<?php
require '../../../../../localfiles/global.php';
if(isset($_GET['id'])){
    if(communicator_client::runfunction('mcservers::validateId("' . $_GET['id'] . '",false);')){
        $id = $_GET['id'];
        txtrw::mktxt($localDir . '/admin/servers/rcon/CURRENT',$id,true);
    }
    else{
        html::loadurl('/admin/servers/');
    }
}
else{
    html::loadurl('/admin/servers/');
}

require 'rcon/html.php';
?>