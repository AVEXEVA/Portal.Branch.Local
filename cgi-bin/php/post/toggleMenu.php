<?php
session_start();
$_SESSION['Toggle_Menu'] = !isset($_SESSION['Toggle_Menu']) || (isset($_SESSION['Toggle_Menu']) && $_SESSION['Toggle_Menu'] == 'not-toggled') ? 'toggled' : 'not-toggled';
?>