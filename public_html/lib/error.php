<?php

$GLOBALS["errors"] = array ();

class ERLEV
{
	const INFO = 0;
	const WARN = 1;
	const ERROR = 3;
};

class ERSYS
{
	const CORE = 0;
	const CACHE = 1;
	const TPL = 2;
	const SQL = 3;
};

function errors_push ($sys, $level, $message)
{
	array_push ($GLOBALS["errors"], array ($sys, $level, $message));
}

function errors_print ()
{
	$v = array ();
	$sys = "";
	$level = "";
	while (($v = array_shift ($GLOBALS['errors'])))
	{
		switch ($v[0]) // subsystem
		{
			case ERSYS::CORE:
				$sys = "Core";
				break;
			case ERSYS::CACHE:
				$sys = "Cache";
				break;
			case ERSYS::TPL:
				$sys = "TPL";
				break;
			case ERSYS::SQL:
				$sys = "SQL";
				break;
			default:
				$sys = "UNKNOWN";
		}
		switch ($v[1]) // level
		{
			case ERLEV::INFO:
				$level = "INFO";
				break;
			case ERLEV::WARN:
				$level = "WARN";
				break;
			case ERLEV::ERROR:
				$level = "ERROR";
				break;
			default:
				$level = "UNKNOWN";
		}
		printf ($sys . ":" . $level . ": " . $v[2] . "\n");
	}
}

?>
