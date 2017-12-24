<?php
//require_once __DIR__ . '/vendor/autoload.php';

//Pepperjam
			date_default_timezone_set('America/Los_Angeles');
			
			$access_key="07cd2b0d64988aedf51c36dad99babf9760905a863caaf82b110048df3184294";
		
			//GET Transaction Details
			//Enter start and end date in command line
			if(isset($argv[2])){
				$pepper_url ='https://api.pepperjamnetwork.com/20120402/advertiser/report/transaction-details/?apiKey=' . $access_key . '&format=JSON&startDate=' . $argv[1] . '&endDate=' . $argv[2];
			//start and end date will be same date
			}else if(isset($argv[1])){
				$pepper_url ='https://api.pepperjamnetwork.com/20120402/advertiser/report/transaction-details/?apiKey=' . $access_key . '&format=JSON&startDate=' . $argv[1] . '&endDate=' . $argv[1];
			//start and end date will be todays date
			}else{
				$pepper_url ='https://api.pepperjamnetwork.com/20120402/advertiser/report/transaction-details/?apiKey=' . $access_key . '&format=JSON&startDate=' . date('Y-m-d') . '&endDate=' . date('Y-m-d');
			}
			
			//var_dump($argv);

			/*$pepper_url ='https://api.pepperjamnetwork.com/20120402/advertiser/report/transaction-details/?apiKey=' . $access_key . '&format=JSON&startDate=2017-07-14&endDate=2017-07-14';*/
			
			//get json from server
			$pepper_data = file_get_contents($pepper_url);

			//decode it
			$pepper_json=json_decode($pepper_data, true);		
			//var_dump($pepper_json);
			$publisher_Array=array();
		
		  //create a publisher array of all publisher ids - this is used as a primary key to associate the terms (Example: "Revised $30 Offer") with publisher ids
			for($i=0; $i< count($pepper_json['data']); $i++){
					$publisher_Array[]=$pepper_json['data'][$i]["publisher_id"];
			}

			echo "Pepper JSON - original data";

			echo "<pre>";
				var_dump($pepper_json);
			echo "</pre>";
	

			//GET Terms based on publisher ids. Example Term would be: "Revised $30 Offer"
			$term_url ='https://api.pepperjamnetwork.com/20120402/advertiser/publisher/?apiKey=' . $access_key . '&format=JSON&id=' . implode(",",$publisher_Array) ;
			$term_data = file_get_contents($term_url);
		
			$term_json=json_decode($term_data, true);
			
			echo "Term JSON";

			echo "<pre>";
				var_dump($term_json);
			echo "</pre>";
		
			$name_Array=array();//term name array
			$term_Array=array();//This array will carry the final data for the term name that is associated with the id

			/*Here we are creating a name array that for each element has an array index (string) of a unique publisher id and a term name as the value (Example: Revised $30 Offer)  - duplicate instances of publisher id will be overwritten, but it doesn't matter because they all have the same term*/
			for($l=0; $l< count($term_json['data']); $l++){
					$name_Array[$term_json['data'][$l]['id']]=$term_json['data'][$l]["term"][0]["name"];	
					
					//Example:   $name_Array[123247] = "Revised $30 Offer"
			}
		
			for($j=0; $j< count($pepper_json['data']); $j++){
					//we create a term array which is a multidimensional array that contains a name and id for each unique publisher
					$term_Array[$j]['id']=$publisher_Array[$j];//the id value with be the publisher_id
					
					// Example:  $term_Array[0]['id'] = "123247"
					
					$term_Array[$j]['name'] = $name_Array[$term_Array[$j]['id']]; //the name value will be the value found START HERE

					// Example:  $term_Array[0]['name'] = $name_Array[$term_Array[0]['id']] <- Value of this will be something like "Revised $30 Offer"

					//If there is no term name, give it the "$30 Flat" default value
					if (!isset($term_Array[$j]['name'])){
						$term_Array[$j]['name']="$30 Flat";	
					}
			}

			echo "Name Array";
			
			echo "<pre>";
				var_dump($name_Array);
			echo "</pre>";

			echo "Term Array";
			
			echo "<pre>";
				var_dump($term_Array);
			echo "</pre>";
			//number of rows in the spreadsheet
			$numRows=count($pepper_json['data']);
			$customerArray=array();//The final array.  It's data will be added to the CSV

			//These are the columns in the eventual spreadsheet
			for($k=0; $k < $numRows; $k++){
				$customerArray[$k][0] = $pepper_json['data'][$k]["transaction_id"];
				$customerArray[$k][1] = $pepper_json['data'][$k]["publisher_id"];
				$customerArray[$k][2] = $pepper_json['data'][$k]["publisher"];
				$customerArray[$k][3] = $pepper_json['data'][$k]["order_id"];
				$customerArray[$k][4] = $pepper_json['data'][$k]["company"];
				$customerArray[$k][5]="";
				$customerArray[$k][6]= $term_Array[$k]['name'];
				$customerArray[$k][7]= $pepper_json['data'][$k]["link_type"];
				$customerArray[$k][8]= "";
				$customerArray[$k][9]= $pepper_json['data'][$k]['sale_amount'];
				$customerArray[$k][10]= $pepper_json['data'][$k]['commission'];
				$customerArray[$k][11]= "";
				$customerArray[$k][12]= $pepper_json['data'][$k]['sale_date'];
				$customerArray[$k][13]= $pepper_json['data'][$k]['status'];
			}

			echo "Customer Array";

			echo "<pre>";
				 var_dump($customerArray);
			echo "</pre>";

		function outputCSV($data) {
			  $output = fopen("C:/Users/petej/Google Drive/daily.csv", "w");
			  foreach ($data as $row){
					fputcsv($output, $row); // here you can change delimiter/enclosure
			  	fclose($output);
				}
		}

			outputCSV($customerArray);
//Pepperjam END

/*
Original Google Sheets API direct implementation.  Couldn't do this because of OAUTH 2.0

define('APPLICATION_NAME', 'Google Sheets API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}*/

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
/*function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setRedirectUri("https://google.com");
  $client->setAccessType('offline');
  $client->setApprovalPrompt('auto');
 

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
   $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
	  // save refresh token to some variable
      $refreshTokenSaved = $client->getRefreshToken();
	  
      // update access token
      $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
	  
	  // pass access token to some variable
      $accessTokenUpdated = $client->getAccessToken();
	  
	  // append refresh token
      $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;
	  
	  //Set the new access token
      $accessToken = $refreshTokenSaved;
      $client->setAccessToken($accessToken);
	  
    
	  file_put_contents($credentialsPath, json_encode($accessTokenUpdated));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
/*function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}*/

// Get the API client and construct the service object.
//$client = getClient();
//$service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
//$spreadsheetId = '1tQ_njQfwtwA0g_SqkqEUOvF18aes5th97qKMd9HFSKE';
//$range = 'Copy of PJ Matchup!A1:N1';
/*$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();*/



/*$values = $customerArray;

$body = new Google_Service_Sheets_ValueRange(array(
  'values' => $values
));

$valueInputOption='USER_ENTERED';

$params = array(
  'valueInputOption' => $valueInputOption
);
$result = $service->spreadsheets_values->append($spreadsheetId, $range,
    $body, $params);*/



/*if (count($values) == 0) {
  print "No data found.\n";
} else {
  //print "Name, Major:\n";
  foreach ($values as $row) {
    // Print columns A and E, which correspond to indices 0 and 4.
    printf("%s, %s\n", $row[0], $row[2]);
  }
}*/
