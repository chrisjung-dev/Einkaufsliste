<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->file = "data.csv";

$app->get('/list', function () use ($app) {

		$file_read = fopen($app->file, 'r');

		if ($file_read !== FALSE) {

			$rows = array();
			while (($data = fgetcsv($file_read, 1000, ";")) !== FALSE) {

				$new = [
					'hash' => $data[0],
					'name' => $data[1],
					'amount' => $data[2],
				];
				$rows[] = $new;
			}
		}

		fclose($file_read);

		$app->response->headers->set('Content-Type', 'application/json');
		$app->response->setBody(json_encode($rows));
	}
);

$app->post('/add', function () use ($app) {

		$json = json_decode($app->request->getBody(), true);
		$json['hash'] = sha1($json['name'] . $json['amount']);

		$new_csv_line = array(
			$json['hash'],
			$json['name'],
			$json['amount'],
		);

		$file_write = fopen($app->file, 'a');
		fputcsv($file_write, $new_csv_line, ';');
		fclose($file_write);

		$request = $app->request;
		$app->response->headers->set('Content-Type', 'application/json');
		$app->response->setBody(json_encode($json));

	}
);

$app->post('/delete', function () use ($app) {

		$json = json_decode($app->request->getBody(), TRUE);
		$todelete = $json['delete'];

		$input = file($app->file);
		$output_write = fopen($app->file, 'w');

		foreach ($input as $line) {

			if (!in_array(explode(";", $line)[0], $todelete)) {
				fputs($output_write, $line);
			}
		}

		$app->response->headers->set('Content-Type', 'application/json');
		$app->response->setBody('{"status":"ok"}');
	}
);

$app->run();