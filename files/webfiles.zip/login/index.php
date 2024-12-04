<?php
require '../../localfiles/global.php';

if(isset($_POST["submit"])){
    $username = preg_replace('/[^a-z0-9_]/', '_', strtolower($_POST['uid']));
    $pwd = $_POST["pwd"];

    if(empty($username) || empty($pwd)){
        header("location: /login/?error=emptyinput");
        exit;
    }

    users::loginUser($username, $pwd);

    exit;
}


html::fullhead("main","Login");
echo '
    <center>
        <h4>&nbsp;</h4>
        <h2>Log In</h2>
        <form method="post">
            <input class="account-form-input" type="text" name="uid" ';
            if(isset($_GET["username"])){echo 'value="' . $_GET["username"] . '"';}
            echo ' placeholder="Username/E-mail">
            <input class="account-form-input" type="password" name="pwd" placeholder="Password">
            <button class="account-form-submit" type="submit" name="submit" onclick="this.innerHTML=\'Logging in...\'">Log In</button>
        </form>
';
if(is_string($useruid)){
    echo "You are already logged in.";
}
if(isset($_GET["error"])){
    if($_GET["error"] == "emptyinput"){
        echo "Please fill in all feilds.";
    }
    else if($_GET["error"] == "userdoesnotexist"){
        echo "That username does not exist.";
    }
    else if($_GET["error"] == "wrongpassword"){
        echo "Incorrect password.";
    }
}
echo '
    </center>
';
html::fullend();