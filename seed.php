<?php

$config = parse_ini_file(__DIR__.'/config.ini');
$connectStr = $config['connection'] . ':host=' . $config['host'] . ';dbname=' . $config['database'];
$connection = new \PDO($connectStr, $config['user'], $config['password']);

$createSql = "CREATE TABLE IF NOT EXISTS directory (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	first_name VARCHAR(30) NOT NULL,
	surname VARCHAR(30) NOT NULL,
	phone VARCHAR(16) NOT NULL
)";

$connection->exec($createSql);

$handle = fopen(__DIR__ .'/db.csv','r');
while ( ($data = fgetcsv($handle) ) !== FALSE ) {
	$records[] = $data;
}

$insertStatement = $connection->prepare("INSERT INTO directory (first_name, surname, phone) VALUES (?,?,?)");
try {
	$connection->beginTransaction();
	foreach ($records as $row)
	{
		$insertStatement->execute($row);
	}
	$connection->commit();
}catch (Exception $e){
	$connection->rollback();
}