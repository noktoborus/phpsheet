<?php
//ini_set ("display_errors", false);

if (!defined ("ROOT_DIR"))
	die ("ROOT_DIR must be defined\n");

if (!defined ("DB_HOST") || !defined ("DB_USER") || !defined ("DB_PASS") || !defined ("DB_NAME"))
	die ("DB_HOST, DB_USER, DB_PASS, DB_NAME must be defined\n");

if (!is_dir (ROOT_DIR . "/tmp") || !is_writable (ROOT_DIR . "/tmp") || !is_readable (ROOT_DIR . "/tmp"))
	die (ROOT_DIR . "/tmp must be exists, has type 'dir' and permissions for write and read\n");

require_once ("error.php");
require_once ("sql.php");
require_once ("tpl.php");

$sql = new SQL (DB_HOST, DB_USER, DB_PASS, DB_NAME);
$tpl = new TPL (ROOT_DIR . "/tpl/", ROOT_DIR . "/tmp/", true);
?>
