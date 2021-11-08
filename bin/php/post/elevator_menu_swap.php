<?php session_start( [ 'read_and_close' => true ] );$_SESSION['Elevator_Menu_Swap'] = !isset($_SESSION['Elevator_Menu_Swap']) || $_SESSION['Elevator_Menu_Swap'] == 0 ? 1 : 0;?>
