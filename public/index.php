<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

//Default
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});


$app->get('/reservasi', 'getReservasi');
$app->get('/reservasi/:id', 'getReservasiById');
$app->post('/reservasi', 'addReservasi');
$app->put('/reservasi/:id', 'updateReservasi');
$app->delete('/reservasi/:id', 'deleteReservasi');

$app->run();



//Tampilkan list reservasi online
function getReservasi(Request $request, Response $response) {
	$sql = "select * FROM reservasi ORDER BY rsv_date DESC";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$reservasi = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

    //$response->setStatus(200);
    $response->withHeader('Content-type', 'application/json');
    //header('Content-Type: application/json');
		//echo json_encode($reservasi);
    $response->withJson($reservasi);
    return $response;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


//Tambah reservasi ke database
function addReservasi(Request $request) {
	//$request = Slim::getInstance()->request();
	$reservasi = json_decode($request->getBody());

  $sql = "INSERT INTO 'reservasi'(rsv_no', 'rsv_date', 'rsv_arrival', 'rsv_departure', 'rsv_source', 'rsv_Fname', 'rsv_Lname')
  VALUES
  (:rsv_no,:rsv_date,:rsv_arrival,:rsv_departure,:rsv_source,:rsv_Fname,:rsv_Lname)";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("rsv_no", $reservasi->rsv_no);
		$stmt->bindParam("rsv_date", $reservasi->rsv_date);
		$stmt->bindParam("rsv_arrival", $reservasi->rsv_arrival);
		$stmt->bindParam("rsv_departure", $reservasi->rsv_departure);
    $stmt->bindParam("rsv_source", $reservasi->rsv_source);
		$stmt->bindParam("rsv_Fname", $reservasi->rsv_Fname);
		$stmt->bindParam("rsv_Lname", $reservasi->rsv_Lname);

		$stmt->execute();
		$reservasi->id = $db->lastInsertId();
		$db = null;
		echo json_encode($reservasi);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}



function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="";
	$dbname="grabbing";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
