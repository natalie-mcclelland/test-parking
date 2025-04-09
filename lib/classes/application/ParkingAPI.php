<?php

// Define class namespace.
namespace UoSParkingApi;

// Include class files.
require_once(dirname(__FILE__) . "\..\..\common/CommonSettings.php");
require_once(dirname(__FILE__) . "\ParkingApplication.php");

/**
 * Zatpark API Integration.
 *
 * This class provides the functionality to interact with ZatPark's API.
 * 
 * @author UIT Web <uit-web@sunderland.ac.uk>
 * @copyright 2023 University of Sunderland
 * @license Proprietary
 * @version 1.0
 */
class zatpark {
    
  /**
   * The default options used when establishing connections using 'cURL'.
   * 
   * This property is initialised in the class constructor.
   * 
   * @var array
   */
  protected $curlDefaultOpts;
    
  /**
   * The prefix for the URL of the API endpoint (without trailing slash).
   * 
   * @var string
   */
  protected $apiEndpointPrefix = "https://api.zatpark.com/api/v2";
  
  /**
   * The access code.
   * 
   * @var string
   */
  // DEV protected $apiAccessCode = "IWJGr8mcOZPP:a7pg5TTSWFe5fRYXnSjRTcnFzslzErsKJue8PomuzStlg42L2lPa02N6zPMjVYeXoNxSWWCKQ==";
  protected $apiAccessCode = "wLsCO9Rl7FWCe5Ih0+JnG4PM2mBfa7CZS9Nb2Pd5sR+Y7cmJNGR6f16TMwcdSugiSrBs9Qh3+x7ztR9BQ8gDpw==";

  /**
   * The site code.
   * 
   * @var string
   */
  // DEV protected $apiSiteCode = "sunderland";  
  protected $apiSiteCode = "135";  

  /**
   * API return codes
   * 
   * @var string
   */
  protected $returnCodes = array(
    "0"=>"Transaction was OK",
    "1"=>"Missing required fields",
    "2"=>"Record cannot be found",
    "3"=>"Charges do not exist",
    "5"=>"Instalment plan error",
    "8"=>"Image file exceeds maximum file size",
    "9"=>"Incorrect currency submitted",
    "10"=>"Incorrect setup of request",
    "11"=>"No records found",
    "12"=>"Configuration error",
    "20"=>"Payment accepted",
    "25"=>"Duplicate transaction",
    "30"=>"Error",
    "31"=>"Site does not support permit class",
    "40"=>"Ticket is part of a claim",
    "50"=>"Permission denied"
  );

  
  /**
   * Constructor.
   * 
   * @since 1.0.0
   */
  function __construct() {    
      // Set the 'cURL' options using the common settings.
      $this->curlDefaultOpts = \UosCommon\CommonSettings::$curlDefaultOpts;
  }
    

  /**
   * Initiate HTTP GET request to the API
   * 
   * @param string $apiEndpoint Full endpoint to connect to.
   * @throws \Exception If unable to connect to remote server or the request was made but there was an error.
   * @return string Decoded JSON data.
   */
  public function executeApiGet($apiEndpoint, $debug = false) {
      
    // Setup the connection to the data source
    $dataRequest = curl_init();
    curl_setopt($dataRequest, CURLOPT_URL, "{$this->apiEndpointPrefix}{$apiEndpoint}");
    curl_setopt_array($dataRequest, $this->curlDefaultOpts);
    curl_setopt($dataRequest, CURLOPT_HTTPHEADER, array(
      "HTTPS_AUTH:  {$this->apiAccessCode}"
    ));
    
    // Execute the transaction and capture any errors
    $dataResult = curl_exec($dataRequest);
    $dataError = curl_error($dataRequest);
    $dataErrorNum = curl_errno($dataRequest);
    $httpStatus = curl_getinfo($dataRequest, CURLINFO_HTTP_CODE);
    
    // Close the connection
    curl_close($dataRequest);

    if ($debug) {
      print("<pre>");
      print("<p>===== DEBUG ==================</p>");
      print("<p>{$this->apiEndpointPrefix}{$apiEndpoint}</p>");
      print("<p>===== RESULT =================</p>");
      print_r($dataResult);
      print("<p>====== ERROR =================</p>");
      print_r($dataError);
      print("<p>====== ERROR NUMBER ==========</p>");
      print_r($dataErrorNum);
      print("<p>====== HTTP STATUS============</p>");
      print_r($httpStatus);
      print("</pre>");
    }

    // Check if there was a response
    if ($dataError) {
      // There was a problem connecting to the remote server
      throw new \Exception("Unable to connect to the API. CURL error: '{$dataError}' ({$dataErrorNum}).");
        
    } else if (intval($httpStatus / 100) >= 4) {
      // The request was made but there was an error
      throw new \Exception("The connection was made to the API but there was an error whilst doing so. HTTP Status: {$httpStatus}.");
        
    } else {
      // The request was successful so decode the JSON string
      $decodedData = json_decode($dataResult);
      
      // Return the data
      return $decodedData;
    }
  }

  /**
   * Initiate HTTP POST request to the API
   * 
   * @param string $apiEndpoint Full endpoint to connect to.
   * @throws \Exception If unable to connect to remote server or the request was made but there was an error.
   * @return string Decoded JSON data.
   */
  public function executeApiPost($apiEndpoint, $apiParams, $debug = false) {
    $postParams = ["permit_data"=>$apiParams];

    // Setup the connection to the data source
    $dataRequest = curl_init();
    curl_setopt($dataRequest, CURLOPT_URL, "{$this->apiEndpointPrefix}{$apiEndpoint}");
    curl_setopt_array($dataRequest, $this->curlDefaultOpts);
    curl_setopt($dataRequest, CURLOPT_POSTFIELDS, $postParams);
    curl_setopt($dataRequest, CURLOPT_HTTPHEADER, array(
      "HTTPS_AUTH:  {$this->apiAccessCode}"
    ));
    
    // Execute the transaction and capture any errors
    $dataResult = curl_exec($dataRequest);
    $dataError = curl_error($dataRequest);
    $dataErrorNum = curl_errno($dataRequest);
    $httpStatus = curl_getinfo($dataRequest, CURLINFO_HTTP_CODE);
    
    // Close the connection
    curl_close($dataRequest);

    if ($debug) {
      print("<pre>");
      print("<p>===== DEBUG ==================</p>");
      print("<p>{$this->apiEndpointPrefix}{$apiEndpoint}</p>");
      print("<p>===== RESULT =================</p>");
      print_r($dataResult);
      print("<p>====== ERROR =================</p>");
      print_r($dataError);
      print("<p>====== ERROR NUMBER ==========</p>");
      print_r($dataErrorNum);
      print("<p>====== HTTP STATUS============</p>");
      print_r($httpStatus);
      print("</pre>");
    }

    // Check if there was a response
    if ($dataError) {
      // There was a problem connecting to the remote server
      throw new \Exception("Unable to connect to the API. CURL error: '{$dataError}' ({$dataErrorNum}).");
        
    } else if (intval($httpStatus / 100) >= 4) {
      // The request was made but there was an error
      throw new \Exception("The connection was made to the API but there was an error whilst doing so. HTTP Status: {$httpStatus}");
        
    } else {
      // The request was successful so decode the JSON string
      $decodedData = json_decode($dataResult);
      
      // Return the data
      return $decodedData;
    }
  }  
  

  /* 
    * ************************************
    * * Methods to interact with the API *
    * ************************************
    */

    /**
     * Checks if vehicle is on the allow list.
     * 
     * @param string $vrn Registration number.
     * @param string $reference  If a session is added via the API and supplied with a reference number, check this value matches.
     * @return string Decoded JSON data.
     */
    public function getPermit($vrm, $reference = null) {
      // Remove whitespace from vrm and reference
      $vrm = $this->formatReferenceNo($vrm);
      $reference = $this->formatReferenceNo($reference);
      // Call API
      $apiEndpoint = '/check_whitelist?vrm='.$vrm;
      if(!empty($reference)) $apiEndpoint .= '&reference_no='.$reference;
      $decodedData = $this->executeApiGet($apiEndpoint);
      return $decodedData;
    }

    /**
     * Checks if vehicle is active (i.e. on the allow list and does not have a close_date).
     * 
     * @param string $vrn Registration number.
     * @param string $reference  If a session is added via the API and supplied with a reference number, check this value matches.
     * @return boolean True if the vehicle is on the allow list and active, otherwise false.
     */    
    public function isPermitActive($vrm, $reference = null) {
      // Remove whitespace from vrm and reference
      $vrm = $this->formatReferenceNo($vrm);
      $reference = $this->formatReferenceNo($reference);

      // Get permit data
      $permit = $this->getPermit($vrm, $reference);

      // Get current timestamp
      $now = date("Y-m-d H:i:s");

      // If status is not 0, the permit does not exist or is not active
      if($permit->status_code > 0) return false;

      foreach($permit->data as $entry) {
        if (empty($entry->close_date) || (!empty($entry->close_date) && $now <= $entry->close_date)) {
          // If no closing date or if closing date is after today, permit is valid
          return true;
        }
      } 
      
      // Permit is not active
      return false;
    }

    /**
     * Removes a vehicle from the allow list.
     * 
     * @param string $vrn Registration number.
     * @param string $reference  If a session is added via the API and supplied with a reference number, check this value matches.
     * @return array $returnVar containing success (true/false) and message.
     */    
    public function cancelPermit($vrm, $reference) {
      // Remove whitespace from vrm and reference
      $vrm = $this->formatReferenceNo($vrm);
      $reference = $this->formatReferenceNo($reference);

      $apiEndpoint = "/cancel_permit";
      $params = array(
        "vrm"=>"{$vrm}",
        "reference_no"=>"{$reference}"
      );
      $apiParams = json_encode($params);
      $result = $this->executeApiPost($apiEndpoint, $apiParams);

      switch ($result->status_code) {
        case 0:
          // Success
          $returnVar = array(["success"=>true, "status"=>"{$result->status_code}", "message"=>"Permit for {$vrm} has been successfully cancelled via the API."]);
          break;
        
        case 25:
          // Permit previously cancelled
          $returnVar = array(["success"=>false, "status"=>"{$result->status_code}", "message"=>"Permit for {$vrm} has already been cancelled via the API on {$result->previous_transaction_date}."]);
          break;

        default:
          $returnVar = array(["success"=>false, "status"=>"{$result->status_code}", "message"=>"Unable to cancel permit via the API for {$vrm}: {$result->message}."]);
      }

      return $returnVar[0];
    }    

    /**
     * Adds a vehicle to the allow list.
     * 
     * @param string $startDate Permit valid from date.
     * @param string $endDate Permit expiry date.
     * @param array $vehicle Array containing vehicle details: vrm, make, colour
     * @param array $driver Array containing driver details: salutation, first_name, last_name
     * @return array $returnVar containing success (true/false) and message.
     */       
    public function addPermit($startDate, $endDate, $vehicle, $driver, $referenceNo) {

      // Ensure vehicle parameter contains valid data
      if( !is_array($vehicle) || !array_key_exists("vrm",$vehicle) || !array_key_exists("make",$vehicle) || !array_key_exists("colour",$vehicle) ) {
          throw new \Exception("Invalid vehicle details passed to add permit API method.");
      }

      // Ensure driver parameter contains valid data
      if( !is_array($driver) || !array_key_exists("salutation",$driver) || !array_key_exists("first_name",$driver) || !array_key_exists("last_name",$driver) ) {
          throw new \Exception("Invalid driver details passed to add permit API method.");
      }

      // Remove whitespace from vrm and reference
      foreach($vehicle as $key=>$value) {
        if ($key == "vrm") $vehicle['vrm'] = $this->formatReferenceNo($value);
      }   
      $referenceNo = $this->formatReferenceNo($referenceNo);

      // Define API endpoint
      $apiEndpoint = "/add_permit";

      // Define API parameters
      $params = array(
        "site_code"=>"{$this->apiSiteCode}",
        "start_date"=>"{$startDate}",
        "end_date"=>"{$endDate}",
        "vehicle_details"=>$vehicle,
        "driver_details"=>$driver,
        "reference_no"=>$referenceNo
      );  
      $apiParams = json_encode($params);  

      // Execute API call
      $result = $this->executeApiPost($apiEndpoint, $apiParams);  

      switch ($result->status_code) {
        case 0:
          // Success
          $returnVar = array(["success"=>true, "status"=>"{$result->status_code}", "message"=>"Vehicle {$vehicle['vrm']} has been successfully registered via the API."]);
          break;

        case 25:
            // Permit already active
            $returnVar = array(["success"=>false, "status"=>"{$result->status_code}", "message"=>"Permit for {$vehicle['vrm']} is already registered with the API."]);
            break;
        
        default:
          $returnVar = array(["success"=>false, "status"=>"{$result->status_code}", "message"=>"Unable to register vehicle {$vehicle['vrm']} via the API: {$result->message}."]);
      }

      return $returnVar[0];
    }

    public function updatePermit($endDate, $vehicle, $referenceNo) {

      // Ensure vehicle parameter contains valid data
      if( !is_array($vehicle) || !array_key_exists("vrm",$vehicle) ) {
        throw new \Exception("Invalid vehicle details passed to update permit API method.");
      }

      // Remove whitespace from vrm and reference
      foreach($vehicle as $key=>$value) {
        if ($key == "vrm") $vehicle['vrm'] = $this->formatReferenceNo($value);
      }   
      $referenceNo = $this->formatReferenceNo($referenceNo);

      // Define API endpoint
      $apiEndpoint = "/update_permit";

      // Define API parameters
      $params = array(
        "end_date"=>"{$endDate}",
        "vehicle_details"=>$vehicle,
        "reference_no"=>$referenceNo,
        "site_code"=>"{$this->apiSiteCode}"
      );  
      $apiParams = json_encode($params);  

      // Execute API call
      $result = $this->executeApiPost($apiEndpoint, $apiParams);

      switch ($result->status_code) {
        case 0:
          // Success
          $returnVar = array(["success"=>true, "status"=>"{$result->status_code}", "message"=>"Vehicle {$vehicle['vrm']} has been successfully updated via the API."]);
          break;

        case 2:
            // Vehicle not found
            $returnVar = array(["success"=>false, "status"=>"{$result->status_code}", "message"=>"Vehicle {$vehicle['vrm']} not found so unable to update via the API."]);
            break;
       
        default:
          $returnVar = array(["success"=>false, "status"=>"{$result->status_code}", "message"=>"Unable to update vehicle {$vehicle['vrm']} via the API: {$result->message}."]);
      }

      return $returnVar[0];
    }


    public function formatReferenceNo ($referenceNo) {
      return preg_replace("/\s+/", "", trim(strtoupper($referenceNo)));
    }
}

?>