<?php
require 'database.class.php';

function prompt() {
	echo "db> ";
}

$database = new Database();

prompt();
$stdin = fopen('php://stdin', 'r');
while ($line = fgets($stdin)) {
	$line = str_replace("\n", "", $line);
	if (strtoupper($line) == 'END') {
		die("Exiting\n");
	}
	if (strlen($line) > 0) {
		try {
			$response = $database->execute($line);
			if (!is_null($response)) {
				echo $response . "\n";
			}
		}
		catch (Exception $e) {
			echo $e->getMessage() . "\n";
		}
	}
	prompt();
}