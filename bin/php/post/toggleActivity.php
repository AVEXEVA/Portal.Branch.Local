<?php session_start( [ 'read_and_close' => true ] ); $_SESSION['toggleActivity'] = !isset($_SESSION['toggleActivity']) || $_SESSION['toggleActivity'] == 0 ? 1 : 0;?>
