<?php
define ("ROOT_DIR", dirname (__FILE__));
require_once ("../define.php");
require_once ("lib/main.php");

$tpl->display ("main", "top");

errors_print ();
?>
