<?php
$host = 'localhost';
$dbname = 'task';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host; dbname=$dbname; charset=utf8";

$opt = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false
];

try {
	$pdo = new PDO($dsn, $user, $pass, $opt);
	//echo 'DB is connect';
} catch(PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}
?>