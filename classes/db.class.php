<?php 
	
	class db_conn {
		
		private $db;
		
		private $driver;

		private $db_flashcards = array(
			"host" => '',
			"db" => '',
			"charset" => 'utf8',
			"user" => '',
			"pass" => '',
			"port" => 3306,
			"opt" => array(
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			)
		);
		
		 public function __construct(){
			$this->connect($this->db_flashcards['host'],
				$this->db_flashcards['db'],
				$this->db_flashcards['user'],
				$this->db_flashcards['pass'],
				$this->db_flashcards['port'],
				$this->db_flashcards['charset'],
				$this->db_flashcards['opt']
			);
		}
		
		private function connect($host,$db,$user,$pass,$port,$charset,$options){
			try {
				$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
				$this->db = new PDO($dsn, $user, $pass, $options);
				$this->driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
			} catch (PDOException $e) {
				print "MySQL Error:" . $e->getMessage() . "<br>";
				print "This error is usually caused because your MySQL credentials are incorrect!";
				die('');
			}
		}
		
		public function Query($query,$params = array()){ // PDO allows prepared statements for no sql injection
			
			if (!isset($params) || !is_array($params)) {
				$params = array($params);
			}
			
			$preparedQ = $this->db->prepare($query);
		
			if (!empty($params)) {
				$preparedQ->execute($params);
			} else {
				$preparedQ->execute();
			}
			return $preparedQ;
		}

		public function QueryTop($query,$params = array()){
			$data = $this::Query($query,$params)->fetchAll();

			if (array_key_exists(0,$data)){
				return $data[0];
			}else{
				return array();
			}
		}
		
		public function GetDriver(){
			return $this->driver;
		}
	}
 ?>