<?php
header('Content-type: application/json');
require '../../../../../../localfiles/global.php';

$num = txtrw::readtxt($localDir . '/admin/servers/rcon/CURRENT');

$response = array();
if(!isset($_POST['cmd'])){
    $response['status'] = 'error';
    $response['error'] = 'Empty command';
}
else{
    $result = communicator_client::runfunction('mcservers::sendCommand("002","say hi",true)');
    if($result !== false){
        $response['status'] = 'success';
        $response['command'] = $_POST['cmd'];
        $response['response'] = $result;
    }
    else{
        $response['status'] = 'error';
        $response['error'] = 'RCON connection error';
    }
}

echo json_encode($response);