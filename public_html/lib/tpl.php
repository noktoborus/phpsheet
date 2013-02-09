<?php

class TPLspace
{
	var $tmpdir;
	var $tpl;
	var $src; // source file
	var $dst; // destination file
	var $data_arrays; // array with callbacks
	var $namespace; // namespace current tpl
	// subtpl
	var $subspace; // sub namespace
	var $startline;
	function __construct ($tpl, $srcfile, $namespace, $_data_arrays, $tmpdir, $offset = -1, $length = NULL)
	{
		$this->src = $srcfile;
		$this->tpl = $tpl;
		$this->data_arrays = $_data_arrays;
		// TODO write file, process
		if ($offset == -1)
		{
			$this->dst = $srcfile;
		}
		else
		{
			// generate filename
			$this->dst = $tmpdir . "subtpl." . $tpl . "@" . $namespace . ".php";
			// generate content
			$conte = file ($srcfile);
			if (!$conte)
			{
				errors_push (ERSYS::TPL, ERLEV::ERROR, "can't read file '" . $srcfile . "', need for template '" . $tpl . "', namespace '" . $namespace . "'");
				return;
			}
			$conte = array_slice ($conte, $offset, $length);
			if (!file_put_contents ($this->dst, implode ("\n", $conte))
			{
				errors_push (ERSYS::TPL, ERLEV::ERROR, "can't write file '" . $this->dst . "', need for template '" . $tpl . ", namespace '" . $namespace . "'");
				return;
			}
		}

	}
	
	function __destruct ()
	{
		if ($this->dst != $this->src)
			unset ($this->dst);
	}
	
	function call_data ()
	{
		if (in_array ($this->namespace, $this->data_arrays))
		{
			$data = call_user_func ($this->data_arrays[$this->namespace], $this->namespace);
			if (!empty ($data))
				return $data;
		}
		errors_push (ERSYS::TPL, ERLEV::WARN, "template '" . $this->tpl . "' (namespace '" . $this->namespace . "') has no data");
		return array ();
	}

	function sub_prep ($space, $lineno)
	{
		// skip another spacename
		if (!$this->space)
		{
			$this->subspace = $space;
			$this->startline = $lineno;
		}
		elseif ($this->space == $space)
		{
			errors_push (ERSYS::TPL, ERLEV::WARN, "template '" . $this->tpl . "'(namespace '" . $this->namespace . "." $space"') has more than one header");
		}

	}

	function sub_proc ($space, $lineno)
	{
		$subtpl = NULL;
		if (empty ($this->subspace))
		{
			errors_push (ERSYS::TPL, ERLEV::WARN, "header for namespace '" . $this->namespace . "." . $space "' not defined in template '" . $this->tpl . "'");
			return;
		}
		// skip another subsubtpl
		if ($this->subspace != $space)
			return;
		/* TODO: allow filename */
		$subtpl = new TPLspace ($this->tpl, $this->dst, $this->namespace . "." . $space, $this->data_arrays, $this->startline, $this->startline - $lineno);
		$subtpl->proc ();
	}

	function proc ()
	{
		// opts
		$_ = array ();
		$repeat = false;
		// if array from args is not array, try use self root data
		$_data = $this->call_data ($this->namespace);
		// check opts
		if (is_array ($_data))
		{
			if (in_array ("_", $_data))
			{
				if (is_array ($_data["_"]))
				{
					$_ = $_data["_"];
					if (in_array ("repeat", $_) && $_["repeat"] === true)
						$repeat = true;
				}
				unset ($_date["_"]);
			}
		}
		if ($repeat)
		{
			// repeat
			while (($__data == array_shift ($_data)))
			{
				if (
				$data = $__data;
				unset $__data;
				if (!include ($this->src))
					errors_push (ERSYS::TPL, ERLEV::WARN, "template '" . $this->tpl . "' (namespace '" . $this->namespace . "'), include ('" . $this->src . "') failed");
			}
		}
		else
		{
			// simple include
			// pass $data to tpl
			$data = $_data;
			unset ($_data);
			if (!include ($this->src))
				errors_push (ERSYS::TPL, ERLEV::WARN, "template '" . $this->tpl . "' (namespace '" . $this->namespace . "'), include ('" . $this->src . "') failed");
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
