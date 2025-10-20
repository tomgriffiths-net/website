<?php
$skipAuth = true;
require '../../localfiles/global.php';

if(isset($_POST["submit"])){
    $pwd = $_POST["pwd"];

    if(empty($pwd)){
        html::loadurl("/login/?error=emptyinput");
    }

    if(!isset($globalSettings['password'])){
        html::loadurl("/login/?error=server");
    }

    if(password_verify($pwd, $globalSettings['password'])){
        session_start();
        $_SESSION["useruid"] = 'admin';
        html::loadurl('/');
    }
    else{
        html::loadurl('/login/?error=wrongpassword');
    }

    exit;
}


html::fullhead("login","Login");
echo '
    <center>
        <h4>&nbsp;</h4>
        <h2>Log In</h2>
        <form method="post">
            <div class="container-sm mb-3" style="max-width:400px;">
                <input class="form-control" type="password" name="pwd" placeholder="Password">
                <button class="btn btn-success mt-2" type="submit" name="submit" onclick="this.innerHTML=\'Logging in...\'">Log In</button>
            </div>
        </form>
';
if(is_string($useruid)){
    echo "You are already logged in.";
}
if(isset($_GET["error"])){
    if($_GET["error"] == "emptyinput"){
        echo "Please fill in all feilds.";
    }
    else if($_GET["error"] == "wrongpassword"){
        echo "Incorrect password.";
    }
    else if($_GET["error"] == "server"){
        echo "Server error.";
    }
}
echo '
    </center>
';
html::fullend();