<?php

require_once('config.php');
include_once('vendor\custom\members.php');


    $member = new Members();
    print_r( $member->updateSecret('austin', 'password'));
