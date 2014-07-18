<?php

require __DIR__ . '/RedBeanPHP4_0_5/rb.php';

R::setup('sqlite:./database.db');

// freeze, if database is set up and first entries are stored!
// R::freeze(true);

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

/**
 * get a list of entries
 */
$app->get('/list', function () use ($app) {

        $entries = R::findAll('entry');

        $rows = [];
        foreach ($entries as $entry) {
            $rows[] = [
                'hash' => $entry->hash,
                'name' => $entry->name,
                'amount' => $entry->amount
            ];
        }

		$app->response->headers->set('Content-Type', 'application/json');
		$app->response->setBody(json_encode($rows));
	}
);

$app->post('/add', function () use ($app) {

        $response = [];
        $body = json_decode($app->request->getBody(), true);

        $name = filter_var($body['name'], FILTER_SANITIZE_STRING);
        $amount = (int)filter_var($body['amount'], FILTER_SANITIZE_STRING);
        $hash = sha1($name . $amount);

        $existing = R::find('entry', 'hash = ?', [$hash]);
        if (!$existing) {
            $newEntry = R::dispense('entry');
            $newEntry->hash = $hash;
            $newEntry->name = $name;
            $newEntry->amount = $amount;

            try {
                R::store($newEntry);
                $response = ["hash" => $hash, "name" => $name, "amount" => $amount];
            } catch (Exception $e) {
                $response = [];
            }
        }

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode($response));
	}
);

$app->post('/delete', function () use ($app) {

		$json = json_decode($app->request->getBody(), TRUE);
		$todelete = $json['delete'];
        $response = ["status" => "ok"];

        $ids = R::getCol('SELECT id FROM entry WHERE hash IN ("' . implode('","', $todelete) . '")');
        $entries = R::loadAll('entry', $ids);
        if ($entries) {
            try {
                R::trashAll($entries);
            } catch (Exception $e) {
                $response["status"] = "fail";
            }
        }

		$app->response->headers->set('Content-Type', 'application/json');
		$app->response->setBody(json_encode($response));
	}
);
$app->run();