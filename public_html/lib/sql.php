<?php
class SQL {
	var $host;
	var $user;
	var $pass;
	var $db;
	var $link;
	function __construct ($host, $user, $pass, $db)
	{
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
		$this->link = false;
	}

	function __destruct ()
	{
		$this->disconnect ();
	}

	function connect ()
	{
		errors_push (ERSYS::SQL, ERLEV::INFO, "open database '" . $this->db . "' with user '" . $this->user . "'");
		$this->link = mysqli_connect ($this->host, $this->user, $this->pass, $this->db);
		if (!$this->link)
		{
			errors_push (ERSYS::SQL, ERLEV::ERROR, mysqli_connect_error ());
			return false;
		}
		return true;
	}

	function disconnect ()
	{
		if ($this->link)
		{
			errors_push (ERSYS::SQL, ERLEV::INFO, "close database '" . $this->db . "' with user '" . $this->user . "'");
			@mysqli_close ($this->link);
		}
	}

	function query ($sql)
	{
		// autoconnect
		if (!$this->link)
			$this->connect ();
		// first query
		$r = mysqli_query ($this->link, $sql);
		// reconnect if 'server has gone away'
		if (!$r && mysqli_errno ($this->link) == 2006)
		{
			// try reconnect
			$this->disconnect ();
			$this->connect ();
			$r = mysqli_query ($this->link, $sql);
		}
		return $r;
	}
};

?>

