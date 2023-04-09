<?php

namespace Phonebook;

use PDO;

class Directory
{
	/**
	 * Database connection instance.
	 * 
	 * @var $connection
	 */
	private $connection;

	/**
	 * @var config
	 */
	private $config;

	/**
	 * Initialize database connection.
	 */
	public function __construct()
	{
		$this->config = parse_ini_file(__DIR__.'/../config.ini');
		$connectStr = $this->config['connection'] . ':host=' . $this->config['host'] . ';dbname=' . $this->config['database'];
		$this->connection = new PDO($connectStr, $this->config['user'], $this->config['password']);
	}

	/**
	 * Create new record.
	 * 
	 * @param $_POST $request
	 */
	public function addRecord($request)
	{
		if ($request["first_name"] && $request["surname"] && $request["phone"]) {
			setcookie("formError", time() - 3600);
			$data = [
				$request["first_name"],
				$request["surname"],
				$request["phone"],
			];
			$insertStatement = $this->connection->prepare("INSERT IGNORE INTO directory (first_name, surname, phone) VALUES (?,?,?)");
			try {
				$this->connection->beginTransaction();
				$insertStatement->execute($data);
				$this->connection->commit();
			}catch (Exception $e){
				$this->connection->rollback();
			}
		} else {
			setcookie("formError", "All fields are required", time()+5);
		}
		$url = 'http://'.$_SERVER['HTTP_HOST'];
		header('Location: ' . $url);
	}

	/**
	 * Fetch all records.
	 * 
	 * @param $request Request parameters
	 * 
	 * @return $result
	 */
	public function getRecords($request)
	{
		$search = $request["search"] ?? null;

		$selectSql = "SELECT * FROM directory";

		if ($search) {
			$selectSql .= $this->prepareWhere($search);
		}

		$statement = $this->connection->prepare($selectSql);
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);

		return [
			"result" => $result
		];
	}

	/**
	 * Set up search condition.
	 * 
	 * @param String $search
	 * 
	 * @return String
	 */
	public function prepareWhere($search)
	{
		$digits = str_split($search);
		$totalDigits = count($digits);
		$keypad = $this->config["keypad"];
		
		$letters = [];
		foreach($digits as $digit) {
			if(array_key_exists($digit, $this->config["keypad"])) {
				$letters[] = explode(",",$this->config["keypad"][$digit]);
			}
		}
		
		$combinations = $this->getCombinations($letters);
		$where = "";
		if(count($combinations)) {
			$where .= " WHERE ";
			foreach($combinations as $combination) {
				$where .= " first_name LIKE '%" . implode("",$combination) . "%' OR ";
				$where .= " surname LIKE '%" . implode("",$combination) . "%' OR ";
			}
			$where = substr($where, 0, -3);
		}
		
		return $where;
	}

	/**
	 * Create letter combinations.
	 * 
	 * @param array 
	 * 
	 * @return array
	 */
	function getCombinations($letters)
    {
		$combinations = [[]];
        foreach ($letters as $key => $charArray) {
            $tmp = [];
            foreach ($combinations as $value) {
                foreach ($charArray as $character) {
                    $tmp[] = array_merge($value, [$key => $character]);
                }
            }
            $combinations = $tmp;
        }
        return $combinations;
    }
}