<?php
require '../../localfiles/global.php';

session_unset();
session_destroy();
html::loadurl('/');