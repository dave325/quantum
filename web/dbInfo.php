<?php 
	
	class AwsConnect{
	
		// Your AWS Access Key ID, as taken from the AWS Your Account page
		private $aws_access_key_id;
		
		// Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
		private $aws_secret_key;
		
		// The database connection
		private $conn;
		
		// The item Id 
		public $productId;
		// The uri
		public $uri;
		
		//The endpoint
		public $endpoint;
		
		/*
		* Function will return data from amazon directly to user
		* @param string $productId 
		* @param string $accessKey
		* @param string $secretKey
		*
		*/
		public function returnData($productId,$accessKey, $secretKey){
			$uri = "/onca/xml";
			$endpoint = "webservices.amazon.com";
			$params = array(
			    "Service" => "AWSECommerceService",
			    "Operation" => "ItemLookup",
			    "AWSAccessKeyId" => $accessKey,
			    "AssociateTag" => "q0d9b-20",
			    "IdType" => "ASIN",
			    "ItemId" => $productId,
			    "ResponseGroup" => "Images,ItemAttributes,Offers"
			);
			
			// Set current timestamp if not set
			if (!isset($params["Timestamp"])) {
			    $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
			}
			
			// Sort the parameters by key
			ksort($params);
			
			$pairs = array();
			
			foreach ($params as $key => $value) {
			    array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
			}
			
			// Generate the canonical query
			$canonical_query_string = join("&", $pairs);
			
			// Generate the string to be signed
			$string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;
			
			// Generate the signature required by the Product Advertising API
			$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $secretKey, true));
			
			// Generate the signed URL
			$request = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
			//Catch the response in the $response object
			$response = file_get_contents($request);
			//formats the content to xml
			$responseXml = simplexml_load_string($response);
			// encode information to json
			$parsedResponse = json_encode($responseXml);	
			// returns the response to ajax call
			echo $parsedResponse;	
			// end the function
			die();
		}
		/*
		*
		* Will populate the database with item
		* @param string $hostname
		* @param string $username
		* @param string $password
		* @param string $db
		*
		*/
		public function addItem($hostname,$username, $password, $db){
			// Connect to database
			$conn = new mysqli($hostname,$username, $password, $db);
			
			// Escape all the post parameters
			$asin = htmlspecialchars($conn->real_escape_string($_POST['addAsin']));
			$title = htmlspecialchars($conn->real_escape_string($_POST['addTitle']));
			$mpn = htmlspecialchars($conn->real_escape_string($_POST['addMpn']));
			$price = htmlspecialchars($conn->real_escape_string($_POST['addPrice']));
			// Check fro connection error
			if ($conn->connect_error) {
	    		die("Connection failed: " . $conn->connect_error);
			} 
			// SQL statement for inserting values to database
			$sql = "INSERT INTO items(asin, title, mpn, price) VALUES(?,?,?,?)";
			$stmt = $conn->prepare($sql);
			// Add values to sql statement
			$stmt->bind_param('ssss', $asin, $title, $mpn, $price);
			//Executes query and checks for errors
			if ($stmt->execute()) {
				$conn->close();
				return;
			} else {
			    echo "Error:" . $conn->error;
			    $conn->close();
			}
		}
		
		/*
		*
		* Will retrieve database values and return a json version of data
		* @params string $hostname
		* @params string $username
		* @params string $password
		* @params string $db
		*
		*/
		public function retrieveListing($hostname, $username, $password, $db){
			// Array to store data from database
			$items = [];
			//Connect to database
			$conn = new mysqli($hostname,$username, $password, $db);
			if ($conn->connect_error) {
	    		die("Connection failed: " . $conn->connect_error);
			} 
			$sql = "SELECT * FROM items";
			// Checks for results add adds them to array
			if($result = $conn->query($sql)){
				while($row = $result->fetch_assoc()){
					$items[$row['asin']]['asin'] = $row['asin'];
					$items[$row['asin']]['title'] = $row['title'];
					$items[$row['asin']]['mpn'] = $row['mpn'];
					$items[$row['asin']]['price'] = $row['price'];
				}
			}else{
				echo "No Listings";
			}
			// encode results as json 
			$response = json_encode($items, JSON_FORCE_OBJECT);
			// sends json to ajax call
			echo $response;
			// close connection to database
			$conn->close();
			// ends php call
			die();
		}
	}
	if(isset($_POST['itemLookup']) && isset($_POST['asinNum'])){
		$productId = $_POST['asinNum'];
		$accessKey = key;
		$secretKey = key;
		$awsConnect = new AwsConnect();
		$awsConnect->returnData($productId,$accessKey, $secretKey);
	}
	if(isset($_POST['addItemForm'])){
		$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
		$server = $url["host"];
		$username = $url["user"];
		$password = $url["pass"];
		$db = substr($url["path"], 1);
		$awsConnect = new AwsConnect();
		$awsConnect->addItem($server, $username, $password, $db);
		$awsConnect->retrieveListing($server, $username, $password, $db);
	}
	else{
		$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
		$server = $url["host"];
		$username = $url["user"];
		$password = $url["pass"];
		$db = substr($url["path"], 1);
		$awsConnect = new AwsConnect();
		$awsConnect->retrieveListing($server, $username, $password, $db);
	}
	
?>