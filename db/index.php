<?php

$file = 'data.csv';

if (!isset($_GET['action']) && !isset($_POST)) {
	die();
}

switch ($_GET['action']) {

	case 'add':
		$file_write = fopen($file, 'a');

		$json = json_decode(file_get_contents("php://input"), TRUE);

		$new_csv_line = array(
			$json['hash'],
			$json['name'],
			$json['amount'],
		);

		$file_write = fopen($file, 'a');
		fputcsv($file_write, $new_csv_line, ';');
		fclose($file_write);

		break;

	case 'read':

		$file_read = fopen($file, 'r');

		$row = 1;

		if ($file_read !== FALSE) {

			$rows = array();

			while (($data = fgetcsv($file_read, 1000, ";")) !== FALSE) {
				$cols = count($data);

				$new = [
					'hash' => $data[0],
					'name' => $data[1],
					'amount' => $data[2],
				];
				$rows[] = $new;
			}
		}

		fclose($file_read);

		echo json_encode($rows);
		break;

	case 'delete':

		$json = json_decode(file_get_contents("php://input"), TRUE);
		$todelete = $json['delete'];

		$input = file($file);
		$output_write = fopen($file, 'w');

		foreach ($input as $line) {

			if (!in_array(explode(";", $line)[0], $todelete)) {
				fputs($output_write, $line);
			}
		}
		header("Content-Type: application/json");

		echo('{"status":"ok"}');
		break;
}
