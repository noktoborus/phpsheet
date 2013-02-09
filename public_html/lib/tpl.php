<?php

class TPLspace
{
	var $dstpath;
	var $srcpath;
	var $startline;
	var $endline;
	var $namespace; // namespace current tpl
	var $sub; // sub tpl space
	function __construct ()
	{
	}
	
	function __destruct ()
	{
	}
	
	function call_data ($namespace)
	{
	}

	function array_prep ($space, $lineno)
	{
		// TODO: alloc new tplspace, gen filename
	}

	function array_proc ($space, $lineno)
	{
		// TODO:write subtemplate, proccess file
		$array_data = $this->call_data ($this->namespace);
		while (($_data = array_shift ($array_data)))
		{
			include ($this->filename);
		}
	}	
};

class TPL
{
	var $spath;
	var $path;
	var $syskey;
	var $usecache;
	var $_TPL_KEY;
	function __construct ($source_path, $cache_path, $use_cache = true)
	{
		$this->spath = $source_path;
		$this->path = $cache_path;
		$this->syskey = sha1 (php_uname () . phpversion ());
		if (!$cache_path || !$use_cache)
			$this->usecache = false;
		else
			$this->usecache = true;
	}

	function display ($tpl, $subtpl = false, $uniqkey = false)
	{
		$tplsrc = $this->spath . $tpl . ".php";
		$tplpath = $this->path . "tpl." . $this->syskey . "." . $uniqkey . "." . $tpl . ($subtpl ?  "-" . $subtpl : "");
		$tpldatepath = $this->path . "idx." . $this->syskey . "." . $tpl;
		$tpldate = "";
		// check tpl
		if (!file_exists ($tplsrc))
		{
			errors_push (ERSYS::TPL, ERLEV::ERROR, "file '" . $tplsrc . "' from template '" . $tpl . "' not exists");
			printf ("<-- UNKNOWN '" . $tpl . "' -->\n");
			return;
		}
		// check index
		if ($this->usecache && $uniqkey)
		{
			if (file_exists ($tpldatepath))
			{
				$tpldate = file_get_contents ($tpldatepath, NULL, NULL, 0, 20);
			}
			// realy key == 14
			if (strlen ($tpldate) != 14)
			{
				$tpldate = date ("Y\Dz\THis");
				// write key to index
				if (!file_put_contents ($tpldatepath, $tpldate))
				{
					errors_push (ERSYS::TPL, ERLEV::WARN, "can't write index to '" . $tpldatepath . "'");
					// disable cache for current tpl
					$uniqkey = false;
				}
			}
			$tplpath = $tplpath . "@" . $tpldate;
		}
		$tplpath .= ".php";
		if ($this->usecache && $uniqkey && file_exists ($tplpath) && filemtime ($tplpath) >= filemtime ($tplsrc))
		{
			// show cached page
			$this->_TPL_KEY = $tpldate;
			// must be "include $tplpath' or 'include ($tplpath)'?! http://www.php.net/manual/en/function.include.php
			if (!include ($tplpath))
				errors_push (ERSYS::CACHE, ERLEV::WARN, "include ('" . $tplpath . "') failed");
		}
		else
		{
			if ($this->usecache && $uniqkey)
				ob_start ();
			else
				printf ("<!-- BEGIN nocached '" . $tpl . "' -->\n");
			if (!include ($tplsrc))
				errors_push (ERSYS::TPL, ERLEV::WARN, "include ('" . $tplsrc . "') failed");
			// gen page from output
			if (!$this->usecache || !$uniqkey)
				printf ("<!-- END noncached '" . $tpl . "' -->\n");
			else
			{
				// display content
				$content = ob_get_clean ();
				printf ("<!-- BEGIN generated '" . $tpl . "' (key d '" . $tpldate . "') -->\n");
				printf ($content);
				printf ("<!-- END generated '" . $tpl . "' (key d '" . $tpldate . "') -->\n");
				// write contant to cache
				ob_start ();
				printf ("<?php if (!isset (\$this)) { header ('Location: /'); die (); } ?>\n");
				printf ("<!-- BEGIN cached '" . $tpl . "' (key c '" . $tpldate . "' d '<?php printf (\$this->_TPL_KEY); ?>') -->\n");
				printf ($content);
				printf ("<!-- END cached '" . $tpl . "' (key c '" . $tpldate . "' d '<?php printf (\$this->_TPL_KEY); ?>') -->\n");
				$content = ob_get_clean ();
				if (!file_put_contents ($tplpath, $content))
					errors_push (ERSYS::TPL, ERLEV::WARN, "cache tpl to '" . $tplpath . "' failed");
			}
		}
	}

	function recache ($tpl, $uniqkey)
	{
		$tpldatepath = $this->path . "idx." . $this->syskey . "." . $tpl;
		$tpldate = "";
		$tplpath = "";
		if (file_exists ($tpldatepath))
		{
			// remove generated page
			if ($uniqkey)
			{
				$tpldate = file_get_contents ($tpldatepath, NULL, NULL, 0, 20);
				if (strlen ($tpldate) == 14)
				{
					$tplpath = $this->path . "tpl." . $this->syskey . "." . $uniqkey . "." . $tpl . "@" . $tpldate . ".php";
					if (!unlink ($tplpath))
						errors_push (ERSYS::TPL, ERLEV::WARN, "can't unlink cache '" . $tplpath . "'");
				}
			}
			// remove index tpl, force update
			if (!unlink ($tpldatepath))
				errors_push (ERSYS::TPL, ERLEV::WARN, "can't unlink index '" . $tpldatepath . "'");
		}
	}
};

?>
