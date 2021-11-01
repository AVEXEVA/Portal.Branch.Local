<?php session_start(); $_SESSION['toggleActivity'] = !isset($_SESSION['toggleActivity']) || $_SESSION['toggleActivity'] == 0 ? 1 : 0;?>
