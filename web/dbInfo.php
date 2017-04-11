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
		public $uri;
		public $endpoint;
		
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
			$responseXml = simplexml_load_string($response);
			$parsedResponse = json_encode($responseXml);	
			echo $parsedResponse;	
		}
		public function addItem($hostname,$username, $password, $db){
			$asin = mysqli_real_escape_string($_POST['addAsin']);
			$title = mysqli_real_escape_string($_POST['addTitle']);
			$mpn = mysqli_real_escape_string($_POST['addMpn']);
			$price = mysqli_real_escape_string($_POST['addPrice']);
			$conn = new mysqli($hostname,$username, $password, $db);
			if ($this->$conn->connect_error) {
	    		die("Connection failed: " . $conn->connect_error);
			} 
			$sql = "INSERT INTO quantum(asin, title, mpn, price) VALUES($asin,$title,$mpn,$price)";
			if ($conn->query($sql) === TRUE) {
				echo "success";
			} else {
			    echo "Error:";
			}
			$conn->close();
		}
		public function retrieveListing($hostname, $username, $password, $sb){
			$items = array();
			$conn = new mysqli($hostname,$username, $password, $db);
			if ($conn->connect_error) {
	    		die("Connection failed: " . $conn->connect_error);
			} 
			$sql = "SELECT * FROM tableName";
			$result = $conn->query($sql);
			$i = 1;
			if($result->num_rows > 0){
				while($row == $result->fetch_assoc()){
					$items[$i]['asin'] = $row['asin'];
					$items[$i]['title'] = $row['title'];
					$items[$i]['mpn'] = $row['mpn'];
					$items[$i]['price'] = $row['price'];
					$i++;
				}
			}else{
				echo "No Listings";
			}
			$response = json_encode($items);
			echo $response;
			$conn->close();
		}
	}
		$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
		$server = $url["host"];
		$username = $url["user"];
		$password = $url["pass"];
		$db = substr($url["path"], 1);
	if(isset($_POST['itemLookup']) && isset($_POST['asinNum'])){
		$productId = $_POST['asinNum'];
		$accessKey = "AKIAIOWFZ4KTTJAKNLFQ";
		$secretKey = "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";
		$awsConnect = new AwsConnect();
		$awsConnect->returnData($productId,$accessKey, $secretKey);
	}
	if(isset($_POST['addItemForm'])){
		$awsConnect = new AwsConnect();
		$awsConnect->addItem($server, $username, $password, $db);
		$awsConnect->retrieveListing($server, $username, $password, $db);
	}
	else{
		$awsConnect = new AwsConnect();
		$awsConnect->retrieveListing($server, $username, $password, $db);
	}
	
?>