<?php

// ensure we can output the data as we go
ob_implicit_flush(true);

echo '<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" type="text/css" href="/assets/css/iframe.css">
	<link href="//fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet" type="text/css">
</head>

<body>

	<div class="iframe-content">';

		// run the import class
		$maxmind = new Maxmind_csv_import();

echo '</div>

</body>
</html>';

// convert/import a Maxmind CSV export into a MySQL database
class Maxmind_csv_import {
	
	// config settings for the import
	public $config = array(
							'host' 		=> 'localhost',
							'user' 		=> 'root',
							'password' 	=> '',
							'database' 	=> 'geoip',
							'file' 		=> '/pathto/csv.csv',
					);

	// set a timer to report the total time taken
	public $start;

	// class construct function
	public function __construct()
	{
		// start the timer
		$this->start = time();

		// let user know we're starting
		$this->_output('Starting import process...');

		// connect to the db
		$this->mysqli = $this->db_connect();

		// create/recreate csv table
		$this->csv_table();

		// populate csv table from csv
		$this->import_csv();

		// create/recreate cc table
		$this->cc_table();

		// create/recreate ip table
		$this->ip_table();

		// import required data from csv to cc table
		$this->csv_to_cc();

		// import required data from csv to ip table
		$this->csv_to_ip();

		// drop the csv table 
		$this->drop_csv_table();

		// calculate and output time taken
		$this->_time_taken();
	}

	// database connection
	private function db_connect()
	{
		// update on progress
		$this->_output('Connecting to the database...');

		// connect to the db using config
		$mysqli = new mysqli($this->config['host'], $this->config['user'], $this->config['password'], $this->config['database']);

		// if there was an error
		if ($mysqli->connect_error)
			$this->_output('Error connecting to the database! (' . $mysqli->connect_errno . ') '. $mysqli->connect_error, false, true);

		// update on progress
		$this->_output('Successfully connected to the database!', true);

		// return the connection
		return $mysqli;
	}

	// create/recreate csv table
	private function csv_table()
	{
		// update on progress
		$this->_output('Preparing to create csv table...');

		// run the drop table query
		$query = $this->mysqli->query("DROP TABLE IF EXISTS csv;");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to drop csv table (if it exists)!', false, true);

		// run the create table query
		$query = $this->mysqli->query("CREATE TABLE csv (
										  start_ip CHAR(15) NOT NULL,
										  end_ip CHAR(15) NOT NULL,
										  start INT UNSIGNED NOT NULL,
										  end INT UNSIGNED NOT NULL,
										  cc CHAR(2) NOT NULL,
										  cn VARCHAR(50) NOT NULL
										);");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to create/recreate csv table!', false, true);

		// report success
		$this->_output('Successfully created csv table!', true);
	}

	// import the csv file to the csv table
	private function import_csv()
	{
		// update on progress
		$this->_output('Preparing to import csv...');

		// get file from config
		$file = $this->config['file'];

		// prepare query
		$sql = "load data infile '$file' into table csv fields terminated by ',' (start_ip, end_ip, `start`, `end`, cc, cn)";

		// run the query
		$query = $this->mysqli->query($sql);

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to import csv into the csv table!', false, true);

		// report success
		$this->_output('Successfully imported csv to csv table!', true);
	}

	// create/recreate cc table
	private function cc_table()
	{
		// update on progress
		$this->_output('Preparing to create cc table...');

		// run the drop table query
		$query = $this->mysqli->query("DROP TABLE IF EXISTS cc;");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to drop cc table (if it exists)!', false, true);

		// run the create table query
		$query = $this->mysqli->query("CREATE TABLE cc (
										  ci TINYINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
										  cc CHAR(2) NOT NULL,
										  cn VARCHAR(50) NOT NULL
										);");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to create/recreate cc table!', false, true);

		// report success
		$this->_output('Successfully created cc table!', true);
	}

	// create/recreate ip table
	private function ip_table()
	{
		// update on progress
		$this->_output('Preparing to create ip table...');

		// run the drop table query
		$query = $this->mysqli->query("DROP TABLE IF EXISTS ip;");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to drop ip table (if it exists)!', false, true);

		// run the create table query
		$query = $this->mysqli->query("CREATE TABLE ip (
										  start INT UNSIGNED NOT NULL,
										  end INT UNSIGNED NOT NULL,
										  ci TINYINT UNSIGNED NOT NULL
										);");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to create/recreate ip table!', false, true);

		// report success
		$this->_output('Successfully created ip table!', true);
	}

	// import required data from csv to cc table
	private function csv_to_cc()
	{
		// update on progress
		$this->_output('Preparing to import data from csv to cc table...');

		// run the import query
		$query = $this->mysqli->query("INSERT INTO cc SELECT DISTINCT NULL,cc,cn FROM csv;");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to import data from csv to cc table!', false, true);

		// report success
		$this->_output('Successfully imported data from csv to cc table!', true);
	}

	// import required data from csv to ip table
	private function csv_to_ip()
	{
		// update on progress
		$this->_output('Preparing to import data from csv to ip table...');

		// run the import query
		$query = $this->mysqli->query("INSERT INTO ip SELECT start,end,ci FROM csv NATURAL JOIN cc;");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to import data from csv to ip table!', false, true);

		// report success
		$this->_output('Successfully imported data from csv to ip table!', true);
	}

	// drop csv table
	private function drop_csv_table()
	{
		// update on progress
		$this->_output('Preparing to drop csv table...');

		// run the drop table query
		$query = $this->mysqli->query("DROP TABLE csv;");

		// report failure if query was unsuccessful
		if( ! $query)
			$this->_output('Failed to drop csv table!', false, true);

		// report success
		$this->_output('Successfully dropped csv table!', true);
	}

	// output messages to screen
	private function _output($message, $success = false, $error = false)
	{
		// if a success message
		if($success)
		{
			// echo out the message with success class
			echo '<p class="success">'.$message.'</p>';
		}
		// if it's an error
		elseif($error)
		{
			// die with error class
			die('<p class="error">'.$message.'</p>');
		}
		// standard output
		else
		{
			// echo out the message
			echo '<p>'.$message.'</p>';
		}

		//
		ob_flush();
	}

	// caluculate out output time taken
	private function _time_taken()
	{
		// take start away from now
		$finish = time() - $this->start;

		// output time taken
		$this->_output("Import complete! Time taken $finish seconds", true);
	}
}

