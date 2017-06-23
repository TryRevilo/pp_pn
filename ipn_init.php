<?php

require('PaypalIPN.php');

$paypalIPN = new PaypalIPN('sandbox');
$paypalIPN -> run();