<?php
session_start( [ 'read_and_close' => true ] );
$_SESSION['Toggle_Menu'] = !isset($_SESSION['Toggle_Menu']) || (isset($_SESSION['Toggle_Menu']) && $_SESSION['Toggle_Menu'] == 'not-toggled') ? 'toggled' : 'not-toggled';
?>