<?php
require '../localfiles/global.php';

html::fullhead("main","Home");
echo '
    <center>
        <p id="text1">
            This website is allways being updated. If you find eany bugs, please 
            <a href="https://www.tomgriffiths.net/issues/new/" class="link">create a new ticket</a>
            and enter the details of the bug.
        </p>
    </center>
';
html::fullend();
