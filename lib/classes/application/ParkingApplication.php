<?php

// Define class namespace
namespace CarParkingSystem;

// Include classes
require_once(dirname(__FILE__) . "\AppDatabase.php");
require_once(dirname(__FILE__) . "/../core/UoSMail.php");

/**
 * Application form data handler for the Car Parking System.
 *
 * This class provides access to the data within the car parking system.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2017 University of Sunderland
 * @license Proprietary
 * @version 1.2.0
 * @package CarParkingSystem
 */
class ParkingApplication {

    private $debug = false;
    
    /**
     * The date in which data should be retained from.
     * 
     * @var string
     */
    protected $dataDateRetainFrom = "1st September last year";
    
    /**
     * The timestamp prior to which data is regarded as stale.
     * 
     * This is property is initialised in the class constructor.
     * 
     * @var integer
     */
    protected $dateDataStaleBefore;
    
    
    /**
     * Constructor.
     * 
     * @since 1.0.0
     */
    function __construct($debugMode = false) {
        // Initialise settings
        if ($debugMode) $this->debug = true;
        
        // Determine the 'stale before' date
        $this->dateDataStaleBefore = date('Y-m-d', strtotime("{$this->dataDateRetainFrom}"));
    }

    public function isDebugMode() {
        if ($this->debug) {
            return "Debug mode is active.";
        } else {
            return "Debug mode is inactive.";
        }
    }
    
    
    /* 
     * *********************************************************
     * * Methods to obtain general datasets for an application *
     * *********************************************************
     */
    
    /**
     * Get the list of pre-nominal letters (titles) for the applicant name.
     * 
     * @return array Returns an array of pre-nominal letters (titles).
     * @since 1.0.0
     */
    public function getApplicantTitles() {
        $applicantTitles = array(
            'Mr.',
            'Miss',
            'Mrs',
            'Ms.',
            'Dr.',
            'Prof.',
            // 'Revd.',
            // 'Cllr.',
            'Sir.',
            // 'Dame',
            // 'Lord',
            // 'Lady',
        );
        
        return $applicantTitles;
    }
    
    /**
     * Get a description of the attendance mode.
     * 
     * @param string $attendanceMode The attendance mode code.
     * @return string Returns a description of the attendance mode.
     * @since 1.0.0
     */
    public function getApplicantAttendanceMode($attendanceMode) {
        switch (trim($attendanceMode)) {
            case 'ft':
                $modeDescription = "Full Time";
                break;

            case 'pt':
                $modeDescription = "Part Time";
                break;

            default:
                $modeDescription = "Unknown";
                break;
        }
        
        return $modeDescription;
    }
    
    /**
     * Get a list of attendance modes.
     * 
     * @return array Returns an array of attendance modes.
     * @since 1.0.0
     */
    public function getApplicantAttendanceModes() {
        $attendanceModes = array(
            'ft' => "Full Time (20.5 to 37 hours)",
            'pt' => "Part Time (0 to 20 hours)",
        );
        
        return $attendanceModes;
    }
    
    /**
     * Get a description of the permit status code.
     * 
     * @param string $statusCode The permit status code.
     * @return string Returns a description of the permit status.
     * @since 1.0.0
     */
    public function getPermitStatus($statusCode) {
        switch (trim($statusCode)) {
            case 'PD':
                $statusDescription = "Pending Approval";
                break;

            case 'RJ':
                $statusDescription = "Rejected";
                break;

            case 'AP':
                $statusDescription = "Approved, pending printing";
                break;

            case 'IS':
                $statusDescription = "Issued";
                break;

            case 'RP':
                $statusDescription = "Reissued, pending printing";
                break;

            case 'RI':
                $statusDescription = "Reissued";
                break;

            case 'HD':
                $statusDescription = "On Hold";
                break;

            case 'CN':
                $statusDescription = "Cancelled";
                break;

            case 'EX':
                $statusDescription = "Expired";
                break;

            default:
                $statusDescription = "Unknown";
                break;
        }

        return $statusDescription;
    }
    
    /**
     * Get a list of permit statuses.
     * 
     * @return array Returns an array of permit statuses.
     * @since 1.0.0
     */
    public function getPermitStatuses() {
        $permitStatuses = array(
            'PD' => "Pending Approval",
            'RJ' => "Rejected",
            'AP' => "Approved, pending printing",
            'IS' => "Issued",
            'RP' => "Reissued, pending printing",
            'RI' => "Reissued",
            'HD' => "On Hold",
            'CN' => "Cancelled",
            'EX' => "Expired",
        );
        
        return $permitStatuses;
    }
    
    /**
     * Get a list of the available permit types.
     * 
     * @param boolean $publicOnly If set to `TRUE` then only the publically available permits will be returned; a value of
     *   `FALSE` will return all available permits.
     * 
     * @param boolean $includeHidden If set to `TRUE` then the hidden permits will be returned along with those that are
     *   visible within the system; a value of `FALSE` will return only those that are visible.
     * 
     * @return array|null Returns an array containing the available permit types and their details.  Returns `NULL` if no
     *   data has been returned.
     * 
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getPermitTypes($publicOnly = FALSE, $includeHidden = FALSE) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statements
            $sqlStatement = "SELECT permit_type.permit_type_id, permit_type.permit_code, permit_type.description, permit_type.available_public, permit_type.available_system ";
            $sqlStatement .= "FROM permit_type WHERE ";
            if ($includeHidden == TRUE) { $sqlStatement .= "(permit_type.available_system = 'y' OR permit_type.available_system = 'n') "; } else { $sqlStatement .= "permit_type.available_system = 'y' "; }
            if ($publicOnly == TRUE) { $sqlStatement .= "AND permit_type.available_public = 'y' "; }
            $sqlStatement .= "ORDER BY permit_type.description ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit types. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permit types.");
            }
        }
    }
    
    /**
     * Get the permit type.
     * 
     * **Note:** This is an alias of the `getPermitTypeFromID()` method.
     * 
     * @param integer $permitTypeID The ID of the permit type.
     * @return array|null Returns an array containing details of the permit type.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::getPermitTypeFromID() ParkingApplication::getPermitTypeFromID()
     */
    public function getPermitType($permitTypeID) {
        try {
            return $this->getPermitTypeFromID($permitTypeID);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the details of the permit type. [ERROR]".$ex."[/ERROR]");
            } else {
                throw new \Exception("Unable to retrieve the details of the permit type.");
            }
        }
    }
    
    /**
     * Get the permit type from the given permit type ID.
     * 
     * @param integer $permitTypeID The ID of the permit type.
     * @return array|null Returns an array containing details of the permit type.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     * @see \CarParkingSystem\ParkingApplication::getPermitType() ParkingApplication::getPermitType()
     */
    public function getPermitTypeFromID($permitTypeID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statements
            $sqlStatement = "SELECT permit_type.permit_type_id, permit_type.permit_code, permit_type.description, permit_type.available_public, permit_type.available_system ";
            $sqlStatement .= "FROM permit_type WHERE permit_type.permit_type_id = '{$permitTypeID}' ORDER BY permit_type.description ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the details of the permit type. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the details of the permit type.");
            }
        }
    }
    
    
    
    /* 
     * *******************************************************************
     * * Methods to manage or obatain specific data about an application *
     * *******************************************************************
     */
    
    /**
     * Create the applicant's primary details record in the database.
     * 
     * @param string $nameTitle The applicant's title.
     * @param string $firstName The applicant's first name.
     * @param string $surname The applicant's surname.
     * @param string $houseFlatProperty The house/flat number of property name.
     * @param string $addressLine1 The first line of the address.
     * @param string $addressLine2 The second line of the address.
     * @param string $addressLine3 The third line of the address.
     * @param string $postTown The address town/city.
     * @param string $county The address county.
     * @param string $postcode The address postcode.
     * @param boolean $isTermAddress If `y` the address is a term time address, otherwise `n` for a home address.
     * @param string $telephoneNumber The applicant's telephone number.
     * @param string $emailAddress The applicant's email address.
     * @param string $applicationOrigin The origin of the application, whether internal ( `int` ) or external ( `ext` ).
     * @return integer The row ID of the database record, which forms the applicant ID.
     * @throws \Exception If the data cannot be entered into the database.
     * @since 1.0.0
     */
    public function createApplicantRecord($nameTitle, $firstName, $surname, $houseFlatProperty, $addressLine1, $addressLine2, $addressLine3, $postTown, $county, $postcode, $isTermAddress, $telephoneNumber, $emailAddress, $applicationOrigin) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "INSERT INTO applicant (title, first_name, surname, house_flat_property, address_1, address_2, address_3, ";
            $sqlStatement .= "post_town, county, postcode, is_term_address, telephone, email_addr, app_origin, dt_create, dt_modify) VALUES ";
            $sqlStatement .= "('" . $appDatabase->escapeString($nameTitle) . "', '" . $appDatabase->escapeString($firstName) . "', '" . $appDatabase->escapeString($surname) . "', ";
            $sqlStatement .= "'" . $appDatabase->escapeString($houseFlatProperty) . "', '" . $appDatabase->escapeString($addressLine1) . "', '" . $appDatabase->escapeString($addressLine2) . "', ";
            $sqlStatement .= "'" . $appDatabase->escapeString($addressLine3) . "', '" . $appDatabase->escapeString($postTown) . "', '" . $appDatabase->escapeString($county) . "', ";
            $sqlStatement .= "'" . $appDatabase->escapeString($postcode) . "', '{$isTermAddress}', '" . $appDatabase->escapeString($telephoneNumber) . "', '" . $appDatabase->escapeString($emailAddress) . "', ";
            $sqlStatement .= "'{$applicationOrigin}', NOW(), NOW());";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database and get the record ID
            $recordID = $appDatabase->queryDatabase($sqlStatement);
            
            return $recordID;
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the applicant record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the applicant record.");
            }
        }
    }
    
    /**
     * Update the applicant's primary details record in the database.
     * 
     * @param integer $applicantID The applicant ID.
     * @param string $nameTitle The applicant's title.
     * @param string $firstName The applicant's first name.
     * @param string $surname The applicant's surname.
     * @param string $houseFlatProperty The house/flat number of property name.
     * @param string $addressLine1 The first line of the address.
     * @param string $addressLine2 The second line of the address.
     * @param string $addressLine3 The third line of the address.
     * @param string $postTown The address town/city.
     * @param string $county The address county.
     * @param string $postcode The address postcode.
     * @param boolean $isTermAddress If `y` the address is a term time address, otherwise `n` for a home address.
     * @param string $telephoneNumber The applicant's telephone number.
     * @param string $emailAddress The applicant's email address.
     * @throws \Exception If the data could not be updated in the database.
     * @since 1.0.0
     */
    public function updateApplicantRecord($applicantID, $nameTitle, $firstName, $surname, $houseFlatProperty, $addressLine1, $addressLine2, $addressLine3, $postTown, $county, $postcode, $isTermAddress, $telephoneNumber, $emailAddress) {
        // Connect to the database, $isTermAddress
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "UPDATE applicant SET title = '" . $appDatabase->escapeString($nameTitle) . "', first_name = '" . $appDatabase->escapeString($firstName) . "', ";
            $sqlStatement .= "surname = '" . $appDatabase->escapeString($surname) . "', house_flat_property = '" . $appDatabase->escapeString($houseFlatProperty) . "', ";
            $sqlStatement .= "address_1 = '" . $appDatabase->escapeString($addressLine1) . "', address_2 = '" . $appDatabase->escapeString($addressLine2) . "', ";
            $sqlStatement .= "address_3 = '" . $appDatabase->escapeString($addressLine3) . "', post_town = '" . $appDatabase->escapeString($postTown) . "', ";
            $sqlStatement .= "county = '" . $appDatabase->escapeString($county) . "', postcode = '" . $appDatabase->escapeString($postcode) . "', is_term_address = '{$isTermAddress}', ";
            $sqlStatement .= "telephone = '" . $appDatabase->escapeString($telephoneNumber) . "', email_addr = '" . $appDatabase->escapeString($emailAddress) . "', ";
            $sqlStatement .= "dt_modify = NOW() WHERE applicant.applicant_id = '{$applicantID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to update the applicant record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to update the applicant record.");
            }
        }
    }
    
    /**
     * Delete the applicant's primary details record from the database.
     * 
     * @param integer $applicantID The applicant ID.
     * @throws \Exception The `ParkingApplication::deleteApplicantData()` method must be used instead.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::deleteApplicantExtraDetailsRecord() ParkingApplication::deleteApplicantExtraDetailsRecord()
     * @see \CarParkingSystem\ParkingApplication::deleteApplicantData() ParkingApplication::deleteApplicantData()
     */
    public function deleteApplicantRecord($applicantID) {
        throw new \Exception("The 'ParkingApplication::deleteApplicantData()' method must be used instead.");
    }
    
    /**
     * Get the applicant's primary details record.
     * 
     * **Note:** This is an alias of the `getApplicantDetails()` method.
     * 
     * @param integer $applicantID The applicant ID.
     * @return array|null An array containing the applicant's primary information.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::getApplicantDetails() ParkingApplication::getApplicantDetails()
     */
    public function getApplicantRecord($applicantID) {
        try {
            return $this->getApplicantDetails($applicantID);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the applicant record. [ERROR]".$ex."[/ERROR]");
            } else {
                throw new \Exception("Unable to retrieve the applicant record.");
            }
        }
    }
    
    /**
     * Get the applicant's primary details record.
     * 
     * @param integer $applicantID The applicant ID.
     * @return array|null An array containing the applicant's primary information.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     * @see \CarParkingSystem\ParkingApplication::getApplicantRecord() ParkingApplication::getApplicantRecord()
     */
    public function getApplicantDetails($applicantID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT applicant.title, applicant.first_name, applicant.surname, applicant.house_flat_property, applicant.address_1, applicant.address_2, applicant.address_3, ";
            $sqlStatement .= "applicant.post_town, applicant.county, applicant.postcode, applicant.is_term_address, applicant.telephone, applicant.email_addr, applicant.app_origin, ";
            $sqlStatement .= "applicant.dt_create, applicant.dt_modify FROM applicant WHERE applicant.applicant_id = '{$applicantID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the applicant's details. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the applicant's details.");
            }
        }
    }
    
    /**
     * Create the applicant's additional details record in the database.
     * 
     * @param integer $applicantID The applicant ID.
     * @param string $userID The username.
     * @param string $idNumber The student number of staff payroll number.
     * @param string $attendanceMode The applicant's attendance mode, `ft` or `pt`.
     * @param boolean $staffAuthPayroll If the applicant is staff and has chosen an annual permit, they must confirm that
     *   their salary can be deducted by Payroll ( `y` ); otherwise it defaults to `n`.
     * 
     * @param string $carParkUsed The car park the applicant uses.  **NOT CURRENTLY IN USE**
     * @param string $department The applicant's department.  **NOT CURRENTLY IN USE**
     * @param string $swipeCardNumber The serial number of the access control swipe card.
     * @param string $reasonApplying For external applicants, the reason why they are applying.
     * @throws \Exception If the data cannot be entered into the database.
     * @since 1.0.0
     */
    public function createApplicantExtraDetailsRecord($applicantID, $userID = NULL, $idNumber = NULL, $attendanceMode = NULL, $staffAuthPayroll = "n", $carParkUsed = NULL, $department = NULL, $swipeCardNumber = NULL, $reasonApplying = NULL) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "INSERT INTO applicant_add_info (applicant_id, user_id, id_num, attendance, staff_auth_payroll, car_park, department, swipe_card_no, reason_applying) VALUES ";
            $sqlStatement .= "('{$applicantID}', '{$userID}', '" . $appDatabase->escapeString($idNumber) . "', ";
            if(isset($attendanceMode)) {
                $sqlStatement .= "'" . $appDatabase->escapeString($attendanceMode) . "', ";
            } else {
                $sqlStatement .= "null, ";
            }
            $sqlStatement .= "'{$staffAuthPayroll}', '" . $appDatabase->escapeString($carParkUsed) . "', '" . $appDatabase->escapeString($department) . "', ";
            $sqlStatement .= "'" . $appDatabase->escapeString($swipeCardNumber) . "', '" . $appDatabase->escapeString($reasonApplying) . "');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the applicant extra details record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the applicant extra details record.");
            }
        }
    }
    
    /**
     * Update the applicant's additional details record in the database.
     * 
     * @param integer $applicantID The applicant ID.
     * @param string $userID The username.
     * @param string $idNumber The student number of staff payroll number.
     * @param string $attendanceMode The applicant's attendance mode, `ft` or `pt`.
     * @param boolean $staffAuthPayroll If the applicant is staff and has chosen an annual permit, they must confirm that
     *   their salary can be deducted by Payroll ( `y` ); otherwise it defaults to `n`.
     * 
     * @param string $carParkUsed The car park the applicant uses.  **NOT CURRENTLY IN USE**
     * @param string $department The applicant's department.  **NOT CURRENTLY IN USE**
     * @param string $swipeCardNumber The serial number of the access control swipe card.
     * @param string $reasonApplying For external applicants, the reason why they are applying.
     * @throws \Exception If the data could not be updated in the database.
     * @since 1.0.0
     */
    public function updateApplicantExtraDetailsRecord($applicantID, $idNumber = NULL, $attendanceMode = NULL, $staffAuthPayroll = "n", $carParkUsed = NULL, $department = NULL, $swipeCardNumber = NULL, $reasonApplying = NULL) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "UPDATE applicant_add_info SET id_num = '" . $appDatabase->escapeString($idNumber) . "', ";
            if (strlen($attendanceMode)) $sqlStatement .= " attendance = '" . $appDatabase->escapeString($attendanceMode) . "', ";
            $sqlStatement .= "staff_auth_payroll = '{$staffAuthPayroll}', car_park = '" . $appDatabase->escapeString($carParkUsed) . "', ";
            $sqlStatement .= "department = '" . $appDatabase->escapeString($department) . "', swipe_card_no = '" . $appDatabase->escapeString($swipeCardNumber) . "', ";
            $sqlStatement .= "reason_applying = '" . $appDatabase->escapeString($reasonApplying) . "' WHERE applicant_add_info.applicant_id = '{$applicantID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to update the applicant extra details record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to update the applicant extra details record.");
            }
        }
    }
    
    /**
     * Delete the applicant's additional details record from the database.
     * 
     * @param integer $applicantID The applicant ID.
     * @throws \Exception The `ParkingApplication::deleteApplicantData()` method must be used instead.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::deleteApplicantRecord() ParkingApplication::deleteApplicantRecord()
     * @see \CarParkingSystem\ParkingApplication::deleteApplicantData() ParkingApplication::deleteApplicantData()
     */
    public function deleteApplicantExtraDetailsRecord($applicantID) {
        throw new \Exception("The 'ParkingApplication::deleteApplicantData()' method must be used instead.");
    }
    
    /**
     * Get the applicant's additional details record.
     * 
     * **Note:** This is an alias of the `getApplicantExtraDetails()` method.
     * 
     * @param integer $applicantID The applicant ID.
     * @return array|null An array containing the applicant's additional details.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::getApplicantExtraDetails() ParkingApplication::getApplicantExtraDetails()
     */
    public function getApplicantExtraDetailsRecord($applicantID) {
        try {
            return $this->getApplicantExtraDetails($applicantID);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the applicant's additional details record. [ERROR]".$ex."[/ERROR]");
            } else {
                throw new \Exception("Unable to retrieve the applicant's additional details record.");
            }
        }
    }
    
    /**
     * Get the applicant's additional details record.
     * 
     * @param integer $applicantID The applicant ID.
     * @return array|null An array containing the applicant's additional details.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     * @see \CarParkingSystem\ParkingApplication::getApplicantExtraDetailsRecord() ParkingApplication::getApplicantExtraDetailsRecord()
     */
    public function getApplicantExtraDetails($applicantID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT applicant_add_info.user_id, applicant_add_info.id_num, applicant_add_info.attendance, applicant_add_info.staff_auth_payroll, applicant_add_info.car_park, ";
            $sqlStatement .= "applicant_add_info.department, applicant_add_info.swipe_card_no, applicant_add_info.reason_applying FROM applicant_add_info ";
            $sqlStatement .= "WHERE applicant_add_info.applicant_id = '{$applicantID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the applicant's additional details. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the applicant's additional details.");
            }
        }
    }
    
    /**
     * Get the applicant ID for the given username.
     * 
     * @param string $username The username that is associated with an applicant's record.
     * @return string|null Returns the applicant ID that is associated with the username, if present; otherwise returns `NULL`.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getApplicantIDFromUsername($username) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT applicant.applicant_id FROM applicant INNER JOIN applicant_add_info ON applicant.applicant_id = applicant_add_info.applicant_id WHERE ";
            $sqlStatement .= "applicant_add_info.user_id = '{$username}' ORDER BY applicant.applicant_id DESC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0]['applicant_id'];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the applicant ID for the user. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the applicant ID for the user.");
            }
        }
    }
    
    /**
     * Get a list of permits that are associated with the applicant.
     * 
     * @param integer $applicantID The applicant ID.
     * @return array|null Returns an array of permits and their details if there are any applications; otherwise returns `NULL`.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getApplicantPermits($applicantID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT permit.permit_id, permit_type.description AS permit_type_description, permit.permit_serial_no, permit.start_date, permit.end_date, permit.status, permit.print_exclude, ";
            $sqlStatement .= "permit.dt_create, permit.dt_modify FROM permit INNER JOIN permit_type ON permit.permit_type = permit_type.permit_type_id WHERE permit.applicant_id = '{$applicantID}' ";
            $sqlStatement .= "ORDER BY permit.start_date ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permits for the applicant. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permits for the applicant.");
            }
        }
    }
    
    /**
     * Get a list of applicants.
     * 
     * @return array|null Returns a two dimensional array of applicants and their details; otherwise returns `NULL`.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     */
    public function getApplicants() {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT applicant.applicant_id, applicant.title, applicant.first_name, applicant.surname, applicant.house_flat_property, applicant.address_1, applicant.address_2, ";
            $sqlStatement .= "applicant.address_3, applicant.post_town, applicant.county, applicant.postcode, applicant.is_term_address, applicant.telephone, applicant.email_addr, ";
            $sqlStatement .= "applicant.app_origin, applicant.dt_create, applicant.dt_modify, aai.user_id, aai.id_num, aai.attendance, aai.staff_auth_payroll, aai.car_park, aai.department, ";
            $sqlStatement .= "aai.swipe_card_no, aai.reason_applying ";
            $sqlStatement .= "FROM applicant INNER JOIN applicant_add_info AS aai ON applicant.applicant_id = aai.applicant_id ";
            $sqlStatement .= "ORDER BY applicant.applicant_id ASC; ";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the list of applicants. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the list of applicants.");
            }
        }
    }
    
    /**
     * Create a record of the applicant's Blue Badge.
     * 
     * @param integer $applicantID The applicant ID.
     * @param string $serialNumber The serial number of the Blue Badge.
     * @param string $issuer The issuing authority.
     * @param string $validFrom The date the Blue Badge is valid from, in the format `YYYY-MM-DD`.
     * @param string $validTo The date the Blue Badge is valid to, in the format `YYYY-MM-DD`.
     * @param string $scanFileFront The generated filename of the uploaded photo/scan of the front of the Blue Badge.
     * @param string $scanFileBack The generated filename of the uploaded photo/scan of the back of the Blue Badge.
     * @throws \Exception If the data could not be entered into the database.
     * @since 1.0.0
     */
    public function createBlueBadgeRecord($applicantID, $serialNumber, $issuer, $validFrom, $validTo, $scanFileFront, $scanFileBack) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "INSERT INTO blue_badge (applicant_id, serial_num, issuer, valid_from, valid_to, scan_file_front, scan_file_back, dt_create, dt_modify) VALUES ";
            $sqlStatement .= "('{$applicantID}', '" . $appDatabase->escapeString($serialNumber) . "', '" . $appDatabase->escapeString($issuer) . "', '{$validFrom}', '{$validTo}', '{$scanFileFront}', ";
            $sqlStatement .= "'{$scanFileBack}', NOW(), NOW());";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the Blue Badge record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the Blue Badge record.");
            }
        }
    }
    
    /**
     * Update a Blue Badge record.
     * 
     * @param integer $blueBadgeID The database record ID for the Blue Badge.
     * @param string $serialNumber The serial number of the Blue Badge.
     * @param string $issuer The issuing authority.
     * @param string $validFrom The date the Blue Badge is valid from in the format `YYYY-MM-DD`.
     * @param string $validTo The date the Blue Badge is valid to in the format `YYYY-MM-DD`.
     * @param string $scanFileFront The generated filename of the uploaded photo/scan of the front of the Blue Badge.
     * @param string $scanFileBack The generated filename of the uploaded photo/scan of the back of the Blue Badge.
     * @throws \Exception If the data could not be updated in the database.
     * @since 1.0.0
     */
    public function updateBlueBadgeRecord($blueBadgeID, $serialNumber, $issuer, $validFrom, $validTo, $scanFileFront, $scanFileBack) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "UPDATE blue_badge SET serial_num = '" . $appDatabase->escapeString($serialNumber) . "', issuer = '" . $appDatabase->escapeString($issuer) . "', ";
            $sqlStatement .= "valid_from = '{$validFrom}', valid_to = '{$validTo}', scan_file_front = '{$scanFileFront}', scan_file_back = '{$scanFileBack}', dt_modify = NOW() ";
            $sqlStatement .= "WHERE blue_badge.blue_badge_id = '{$blueBadgeID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to update the Blue Badge record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to update the Blue Badge record.");
            }
        }
    }
    
    /**
     * Delete the Blue Badge record from the database.
     * 
     * @param integer $blueBadgeID The database record ID for the Blue Badge.
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     */
    public function deleteBlueBadgeRecord($blueBadgeID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE FROM blue_badge WHERE blue_badge.blue_badge_id = {$blueBadgeID};";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the Blue Badge record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the Blue Badge record.");
            }
        }
    }
    
    /**
     * Get the details of a specific Blue Badge record.
     * 
     * @param integer $blueBadgeID The database record ID for the Blue Badge.
     * @return array|null Returns an array containing details about the Blue Badge.  Returns `NULL` if no data has been
     *   returned.
     * 
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getBlueBadgeRecord($blueBadgeID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "SELECT blue_badge.applicant_id, blue_badge.serial_num, blue_badge.issuer, blue_badge.valid_from, blue_badge.valid_to, blue_badge.scan_file_front, blue_badge.scan_file_back, ";
            $sqlStatement .= "blue_badge.dt_create, blue_badge.dt_modify FROM blue_badge WHERE blue_badge.blue_badge_id = '{$blueBadgeID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the Blue Badge record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the Blue Badge record.");
            }
        }
    }
    
    /**
     * Get the Blue Badge records that are associated with an applicant.
     * 
     * @param integer $applicantID The applicant ID.
     * @return array|null Returns a two dimensional array of Blue Badge records and their details, if present; otherwise
     *   returns `NULL`.
     * 
     * @throws \Exception If the details cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getBlueBadgeRecords($applicantID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT blue_badge.blue_badge_id, blue_badge.applicant_id, blue_badge.serial_num, blue_badge.issuer, blue_badge.valid_from, blue_badge.valid_to, blue_badge.scan_file_front, ";
            $sqlStatement .= "blue_badge.scan_file_back, blue_badge.dt_create, blue_badge.dt_modify FROM blue_badge WHERE blue_badge.applicant_id = '{$applicantID}' ORDER BY blue_badge.dt_create ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the Blue Badge records for the applicant. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the Blue Badge records for the applicant.");
            }
        }
    }
    
    /**
     * Create the permit record in the database.
     * 
     * @param integer $applicantID The applicant ID.
     * @param integer $permitType The permit type ID.
     * @param string $startDate The date the permit is valid from in the format `YYYY-MM-DD`.
     * @param string $endDate The date the permit is valid until in the format `YYYY-MM-DD`.
     * @return interger The row ID of the permit record.
     * @throws \Exception If the details cannot be entered into the database.
     * @since 1.0.0
     */
    public function createPermitRecord($applicantID, $permitType, $startDate, $endDate) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "INSERT INTO permit (applicant_id, permit_type, start_date, end_date, status, print_exclude, dt_create, dt_modify) VALUES ('{$applicantID}', '{$permitType}', ";
            $sqlStatement .= "'{$startDate}', '{$endDate}', 'PD', 'n', NOW(), NOW());";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database and get the record ID
            $recordID = $appDatabase->queryDatabase($sqlStatement);
            
            return $recordID;
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the permit record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the permit record.");
            }
        }
    }
    
    /**
     * Delete the permit record from the database.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @throws \Exception The `ParkingApplication::deletePermitData()` method must be used instead.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::deletePermitData() ParkingApplication::deletePermitData()
     */
    public function deletePermitRecord($permitID) {
        throw new \Exception("The 'ParkingApplication::deletePermitData()' method must be used instead.");
    }
    
    /**
     * Get the details of a specific permit record.
     * 
     * **Note:** This is an alias of the `getPermitDetails()` method.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @return array|null Returns an array containing details about the permit.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::getPermitDetails() ParkingApplication::getPermitDetails()
     */
    public function getPermitRecord($permitID) {
        try {
            return $this->getPermitDetails($permitID);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit record. [ERROR]".$ex."[/ERROR]");
            } else {
                throw new \Exception("Unable to retrieve the permit record.");
            }
        }
    }
    
    /**
     * Get the details of a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @return array|null Returns an array containing details about the permit.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     * @see \CarParkingSystem\ParkingApplication::getPermitDetails() ParkingApplication::getPermitDetails()
     */
    public function getPermitDetails($permitID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT permit.applicant_id, permit.permit_type, permit_type.permit_code AS permit_type_code, permit_type.description AS permit_type_description, permit.permit_serial_no, ";
            $sqlStatement .= "permit.start_date, permit.end_date, permit.status, permit.print_exclude, permit.dt_create, permit.dt_modify FROM permit ";
            $sqlStatement .= "INNER JOIN permit_type ON permit.permit_type = permit_type.permit_type_id WHERE permit.permit_id = '{$permitID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit details. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permit details.");
            }
        }
    }
    
    /**
     * Generates the serial number for a permit record.
     * 
     * Due to the permit serial number prefixes varying in length, either being three or four alpha characters, the serial
     * number generator uses a special process to ensure that a unique sequential number is generated.  Therefore, the
     * serial number consists of a seven digit sequential number that is preceeded by a modified permit type code, which is
     * in one of the following formats:-
     *  + `AAA0`: Three alpha permit type code suffixed with a `0` to form a four character string.
     *  + `AAAA`: Four alpha permit type code.
     * 
     * This allows for **9,999,999** unique numbers for each permit type before the string length will increase.
     * 
     * The serial number is generated using the following sequence:-
     *  1. Get the permit type ID and code for the given permit record.
     *  2. Identify the serial number of the last permit issued:-
     *    - Check the permit records first:-
     *      1. Search the permit records and retrieve the serial number of the last permit issued of that permit type ID.
     *      2. If there is a record, strip the first four characters from the serial number, so that that only the numeric part
     *        remains.
     * 
     *    - Otherwise the previous serial number will be `0`.
     * 
     *  3. Increment the last serial number by `1` and pad the string to seven numbers.  For example: `1` will become `0000001`.
     *  4. Prefix the new serial number with the permit type code to form the full serial number.
     *  5. Update the permit record with the full serial number.
     * 
     * @param interger $permitID The database record ID for the permit.
     * @throws \Exception If there was a problem retrieving from or entering data into the database.
     * @since 1.0.0
     */
    public function generatePermitSerialNumber($permitID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        
        // Get the type of the given permit
        try {
            $currentPermitDetails = $this->getPermitDetails($permitID);
            $currentPermitType = $currentPermitDetails['permit_type'];
            $currentPermitTypeCode = $currentPermitDetails['permit_type_code'];
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the type of the given permit. [ERROR]".$ex."[/ERROR]");
            } else {
                throw new \Exception("Unable to retrieve the type of the given permit.");
            }
        }
        
        
        // Get the serial number of the last permit issued of the same type
        try {
            // Initalise the serial number
            $lastSerialNumber = "TMP00000000";

            // Create the SQL statement
            $sqlStatement = "SELECT permit.permit_serial_no FROM permit WHERE permit.permit_type = '{$currentPermitType}' AND permit.permit_serial_no != '' ORDER BY ";
            $sqlStatement .= "permit.permit_serial_no DESC, permit.permit_id DESC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                $lastSerialNumber = $sqlResult[0]['permit_serial_no'];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to get the serial number of the last permit issued of the same type. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to get the serial number of the last permit issued of the same type.");
            }
        }
      
        // Generate the serial number for the given permit
        try {
            // Remove the prefix (3 alpha + 1 int, or 4 alpha) and increment the numeric by 1, padding it to 7 numbers
            $serialNumberNumeric = str_pad((substr($lastSerialNumber, 4) + 1), 7, "0", STR_PAD_LEFT);
            
            // Create the appropriate prefix for the serial number; if 3 char then suffix a "0" to the prefix, so that it becomes 4 char
            $serialNumberPrefix = ((strlen($currentPermitTypeCode) == 3) ? "{$currentPermitTypeCode}0" : $currentPermitTypeCode);
            
            // Merge the prefix and numeric parts
            $serialNumber = $serialNumberPrefix . $serialNumberNumeric;
            
            // Create the SQL statement and query the database
            $sqlStatement = "UPDATE permit SET permit.permit_serial_no = '{$serialNumber}', dt_modify = NOW() WHERE permit.permit_id = '{$permitID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to generate permit serial number. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to generate permit serial number.");
            }
        }
    }
    
    /**
     * Update the status of a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @param string $statusCode The new status code.
     * @throws \Exception If the data could not be updated in the database.
     * @since 1.0.0
     */
    public function setPermitStatus($permitID, $statusCode) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "UPDATE permit SET permit.status = '{$statusCode}', dt_modify = NOW() WHERE permit.permit_id = '{$permitID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to update the permit status. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to update the permit status.");
            }
        }
    }
    
    /**
     * Set the print export exclusion flag for a permit record.
     * 
     * The flag allows a permit to be excluded from the nightly export process that collates permit data so that they can be
     * printed.  Setting the flag to `y` will cause the permit to be excluded and setting it to `n` will cause it to be
     * included.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @param boolean $exclude Whether the permit should ( `y` ) or shouldn't ( `n` ) be excluded from printing.
     * @throws \Exception If the data cannot be updated in the database.
     * @since 1.0.0
     */
    public function setPermitPrintExcludeFlag($permitID, $exclude = "y") {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "UPDATE permit SET permit.print_exclude = '{$exclude}', dt_modify = NOW() WHERE permit.permit_id = '{$permitID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to update the permit print exclusion flag. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to update the permit print exclusion flag.");
            }
        }
    }
    
    /**
     * Create a note that will be associated with a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @param string $noteText The note text.
     * @param string $noteAuthor The username of user who wrote the note text.
     * @throws \Exception If the data cannot be entered into the database.
     * @since 1.0.0
     */
    public function createPermitNote($permitID, $noteText, $noteAuthor) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "INSERT INTO permit_note (permit_id, note_text, dt_create, user_create) VALUES ('{$permitID}', '" . $appDatabase->escapeString($noteText) . "', NOW(), '{$noteAuthor}');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the permit note. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the permit note.");
            }
        }
    }
    
    /**
     * Get a specific permit note.
     * 
     * @param integer $permitNoteID The database record ID of the permit note.
     * @return array|null Returns the permit note details, otherwise returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getPermitNote($permitNoteID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT permit_note.note_text, permit_note.dt_create, permit_note.user_create, permit_note.dt_modify, permit_note.user_modify FROM permit_note ";
            $sqlStatement .= "WHERE permit_note.permit_note_id = '{$permitNoteID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit note. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permit note.");
            }
        }
    }
    
    /**
     * Get the permit notes that are associated with a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @return array|null Returns a two dimensional array of permit notes and their details, otherwise returns `NULL` if no
     *   data has been returned.
     * 
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getPermitNotes($permitID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT permit_note.permit_note_id, permit_note.note_text, permit_note.dt_create, permit_note.user_create, permit_note.dt_modify, permit_note.user_modify ";
            $sqlStatement .= "FROM permit_note WHERE permit_note.permit_id = '{$permitID}' ORDER BY permit_note.dt_create ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit note. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permit note.");
            }
        }
    }
    
    /**
     * Get the vehicles that are associated with a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @return array|null Returns a two dimensional array of vehicle details, otherwise returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getPermitVehicles($permitID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT vehicle.vehicle_id, vehicle.registration, vehicle.make, vehicle.colour, vehicle.dt_create, vehicle.dt_modify FROM vehicle ";
            $sqlStatement .= "INNER JOIN jn_vehicle_permit ON vehicle.vehicle_id  = jn_vehicle_permit.vehicle_id ";
            $sqlStatement .= "INNER JOIN permit ON jn_vehicle_permit.permit_id = permit.permit_id WHERE permit.permit_id = '{$permitID}' ORDER BY vehicle.registration ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the vehicles for the given permit. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the vehicles for the given permit.");
            }
        }
    }
    
    /**
     * Get the permit and associated applicant records for permits that are in the given status and of the optional permit type.
     * 
     * @param string $statusCode The permit status code.
     * @param string $permitTypeCode The permit type code.
     * @return array|null Returns a two dimensional array of permit and associated applicant records, otherwise returns `NULL`
     *   if no data has been returned.
     * 
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getPermitApplicationsByStatus($statusCode, $permitTypeCode = NULL) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT p.permit_id, pt.permit_code AS permit_type_code, pt.description AS permit_type_description, p.permit_serial_no, p.start_date, ";
            $sqlStatement .= "p.end_date, p.status, p.print_exclude, p.dt_create, p.dt_modify, a.applicant_id, a.title, a.first_name, ";
            $sqlStatement .= "a.surname, aai.id_num, GROUP_CONCAT(UPPER(v.registration) SEPARATOR ', ') as vehicle_registrations ";
            $sqlStatement .= "FROM web_carpark.vehicle AS v INNER JOIN web_carpark.jn_vehicle_permit AS vp ON v.vehicle_id = vp.vehicle_id ";
            $sqlStatement .= "INNER JOIN web_carpark.permit AS p ON p.permit_id = vp.permit_id INNER JOIN web_carpark.permit_type AS pt ON pt.permit_type_id = p.permit_type ";
            $sqlStatement .= "INNER JOIN web_carpark.applicant AS a ON a.applicant_id = p.applicant_id INNER JOIN web_carpark.applicant_add_info AS aai ON aai.applicant_id = a.applicant_id ";
            $sqlStatement .= "WHERE p.status = '{$statusCode}' ";
            if ($permitTypeCode != NULL) ($sqlStatement .= "AND pt.permit_code = '{$permitTypeCode}' ");
            $sqlStatement .= "GROUP BY p.permit_id, permit_type_code, permit_type_description, p.permit_serial_no, p.start_date, p.end_date, p.status, p.print_exclude, p.dt_create, ";
            $sqlStatement .= "p.dt_modify, a.applicant_id, a.title, a.first_name, a.surname, aai.id_num ";
            $sqlStatement .= "ORDER BY p.dt_create ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit applications in the given status. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permit applications in the given status.");
            }
        }
    }
    
    /**
     * Create a transaction log entry that will be associated with a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @param string $transaction A string containing details about the transaction that took place.
     * @param string $username The username of the user who performed the transaction.
     * @throws \Exception If the data could not be entered into the database.
     * @since 1.0.0
     */
    public function createPermitTransactionLogEntry($permitID, $transaction, $username) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "INSERT INTO permit_transaction_log (permit_id, transaction, timestamp, user) VALUES ('{$permitID}', '" . $appDatabase->escapeString($transaction) . "', NOW(), '{$username}');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the permit transaction log entry. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the permit transaction log entry.");
            }
        }
    }
    
    /**
     * Get the transaction log entries for a permit record.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @return array|null Returns a two dimensional array containing transaction log entries, otherwise returns `NULL` if no data
     *   has been returned.
     * 
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getPermitTransactionLogEntries($permitID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT permit_transaction_log.transaction, permit_transaction_log.timestamp, permit_transaction_log.user FROM permit_transaction_log ";
            $sqlStatement .= "WHERE permit_transaction_log.permit_id = '{$permitID}' ORDER BY permit_transaction_log.permit_transaction_id ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permit transaction log entries. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permit transaction log entries.");
            }
        }
    }
    
    /**
     * Create the vehicle record in the database.
     * 
     * @param integer $permitID The database record ID for the permit.
     * @param string $registrationMark The Vehicle Registration Mark.
     * @param string $make The make of the vehicle.
     * @param string $colour The colour of the vehicle.
     * @throws \Exception If the data could not be entered in the database.
     * @since 1.0.0
     */
    public function createVehicleRecord($permitID, $registrationMark, $make, $colour) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        // Initalise the vehicle record ID
        $vehicleRecordID = NULL;
        
        
        // Vehicle record
        try {
            // Create the SQL statement
            $sqlStatement = "INSERT INTO vehicle (registration, make, colour, dt_create, dt_modify) VALUES ('" . $appDatabase->escapeString($registrationMark) . "', ";
            $sqlStatement .= "'" . $appDatabase->escapeString($make) . "', '" . $appDatabase->escapeString($colour) . "', NOW(), NOW());";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database and get the record ID
            $vehicleRecordID = $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the vehicle record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the vehicle record.");
            }
        }
        
        
        // Vehicle permit record
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "INSERT INTO jn_vehicle_permit (vehicle_id, permit_id) VALUES ('{$vehicleRecordID}', '{$permitID}');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to create the vehicle permit record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to create the vehicle permit record.");
            }
        }
    }
    
    /**
     * Update the vehicle record in the database.
     * 
     * @param integer $vehicleID The database record ID for the vehicle.
     * @param string $registrationMark The Vehicle Registration Mark.
     * @param string $make The make of the vehicle.
     * @param string $colour The colour of the vehicle.
     * @throws \Exception If the data cannot be updated in the database.
     * @since 1.0.0
     */
    public function updateVehicleRecord($vehicleID, $registrationMark, $make, $colour) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "UPDATE vehicle SET registration = '" . $appDatabase->escapeString($registrationMark) . "', make = '" . $appDatabase->escapeString($make) . "', ";
            $sqlStatement .= "colour = '" . $appDatabase->escapeString($colour) . "', dt_modify = NOW() WHERE vehicle_id = '{$vehicleID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to update the vehicle record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to update the vehicle record.");
            }
        }
    }
    
    /**
     * Delete the vehicle record from the database.
     * 
     * @param integer $vehicleID The database record ID for the vehicle.
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     */
    public function deleteVehicleRecord($vehicleID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE vehicle, jn_vehicle_permit FROM vehicle INNER JOIN jn_vehicle_permit ON jn_vehicle_permit.vehicle_id = vehicle.vehicle_id ";
            $sqlStatement .= "WHERE vehicle.vehicle_id = {$vehicleID};";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the vehicle record. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the vehicle record.");
            }
        }
    }
    
    /**
     * Get the details of a vehicle record.
     * 
     * **Note:** This is an alias of the `getVehicleDetails()` method.
     * 
     * @param integer $vehicleID The database record ID for the vehicle.
     * @return array|null Returns an array containing details about the permit.  Returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::getVehicleDetails() ParkingApplication::getVehicleDetails()
     */
    public function getVehicleRecord($vehicleID) {
        try {
            return $this->getVehicleDetails($vehicleID);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the vehicle record. [ERROR]".$ex."[/ERROR]");
            } else {
                throw new \Exception("Unable to retrieve the vehicle record.");
            }
        }
    }
    
    /**
     * Get the details of a vehicle record.
     * 
     * @param integer $vehicleID The database record ID for the vehicle.
     * @return array|null Returns an array containing details about the vehicle, otherwise returns `NULL` if no data has been returned.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     * @see \CarParkingSystem\ParkingApplication::getVehicleRecord() ParkingApplication::getVehicleRecord()
     */
    public function getVehicleDetails($vehicleID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and
            $sqlStatement = "SELECT vehicle.registration, vehicle.make, vehicle.colour, vehicle.dt_create, vehicle.dt_modify FROM vehicle WHERE vehicle.vehicle_id = '{$vehicleID}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult[0];
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the vehicle details. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the vehicle details.");
            }
        }
    }
    
    
    
    /* 
     * *******************************************
     * * Methods to bulk delete application data *
     * *******************************************
     */
    
    /**
     * Delete all stale applicant records and associated data.
     * 
     * The data that will be deleted includes:
     *  + Applicants' primary details record
     *  + Applicants' additional details record
     *  + Applicants' Blue Badge records
     *  + Applicants' permit records
     *  + Notes made against a specific permit
     *  + Transaction log for a specific permit
     *  + Vehicle data associated with a specific permit
     * 
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     */
    public function deleteAllStaleApplicantData() {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE applicant, applicant_add_info, blue_badge, permit, permit_note, permit_transaction_log, jn_vehicle_permit, vehicle FROM applicant ";
            $sqlStatement .= "LEFT JOIN applicant_add_info ON applicant_add_info.applicant_id = applicant.applicant_id ";
            $sqlStatement .= "LEFT JOIN blue_badge ON blue_badge.applicant_id = applicant.applicant_id ";
            $sqlStatement .= "INNER JOIN permit ON permit.applicant_id = applicant.applicant_id ";
            $sqlStatement .= "LEFT JOIN permit_note ON permit_note.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN permit_transaction_log ON permit_transaction_log.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN jn_vehicle_permit ON jn_vehicle_permit.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN vehicle ON vehicle.vehicle_id = jn_vehicle_permit.vehicle_id ";
            $sqlStatement .= "WHERE applicant.dt_modify <= DATE('{$this->dateDataStaleBefore}');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the stale applicant records and associated data. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the stale applicant records and associated data.");
            }
        }
    }
    
    /**
     * Get the details for all stale Blue Badge records.
     * 
     * @return array|null Returns a two dimensional array of Blue Badge records and their details, if present; otherwise
     *   returns `NULL`.
     * 
     * @throws \Exception If the details cannot be retrieved from the database.
     * @since 1.2.0
     */
    public function getAllStaleBlueBadgeRecords() {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT blue_badge.blue_badge_id, blue_badge.applicant_id, blue_badge.serial_num, blue_badge.issuer, blue_badge.valid_from, blue_badge.valid_to, blue_badge.scan_file_front, ";
            $sqlStatement .= "blue_badge.scan_file_back, blue_badge.dt_create, blue_badge.dt_modify FROM blue_badge WHERE blue_badge.valid_to <= DATE('{$this->dateDataStaleBefore}') ";
            $sqlStatement .= "ORDER BY blue_badge.dt_create ASC;";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the stale Blue Badge records. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the stale Blue Badge records.");
            }
        }
    }
    
    /**
     * Delete all stale Blue Badge records.
     * 
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     */
    public function deleteAllStaleBlueBadgeData() {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE FROM blue_badge WHERE blue_badge.valid_to <= DATE('{$this->dateDataStaleBefore}');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the stale Blue Badge records. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the stale Blue Badge records.");
            }
        }
    }
    
    /**
     * Delete all stale permit records and associated data.
     * 
     * The data that will be deleted includes:
     *  + Applicants' permit records
     *  + Notes made against a specific permit
     *  + Transaction log for a specific permit
     *  + Vehicle data associated with a specific permit
     * 
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     */
    public function deleteAllStalePermitData() {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE permit, permit_note, permit_transaction_log, jn_vehicle_permit, vehicle FROM permit ";
            $sqlStatement .= "LEFT JOIN permit_note ON permit_note.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN permit_transaction_log ON permit_transaction_log.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN jn_vehicle_permit ON jn_vehicle_permit.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN vehicle ON vehicle.vehicle_id = jn_vehicle_permit.vehicle_id ";
            $sqlStatement .= "WHERE permit.end_date <= DATE('{$this->dateDataStaleBefore}');";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the stale permit records and associated data. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the stale permit records and associated data.");
            }
        }
    }
    
    /**
     * Delete the data that is associated with the specified applicant record.
     * 
     * The data that will be deleted includes:
     *  + Applicant's primary details record
     *  + Applicant's additional details record
     *  + Applicant's Blue Badge records
     *  + Applicant's permit records
     *  + Notes made against a specific permit
     *  + Transaction log for a specific permit
     *  + Vehicle data associated with a specific permit
     * 
     * @param integer $applicantID The applicant ID.
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::deleteApplicantRecord() ParkingApplication::deleteApplicantRecord()
     * @see \CarParkingSystem\ParkingApplication::deleteApplicantExtraDetailsRecord() ParkingApplication::deleteApplicantExtraDetailsRecord()
     */
    public function deleteApplicantData($applicantID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE applicant, applicant_add_info, blue_badge, permit, permit_note, permit_transaction_log, jn_vehicle_permit, vehicle FROM applicant ";
            $sqlStatement .= "LEFT JOIN applicant_add_info ON applicant_add_info.applicant_id = applicant.applicant_id ";
            $sqlStatement .= "LEFT JOIN blue_badge ON blue_badge.applicant_id = applicant.applicant_id ";
            $sqlStatement .= "LEFT JOIN permit ON permit.applicant_id = applicant.applicant_id ";
            $sqlStatement .= "LEFT JOIN permit_note ON permit_note.permit_id = permit.permit_id ";
            $sqlStatement .= "LEFT JOIN permit_transaction_log ON permit_transaction_log.permit_id = permit.permit_id ";
            $sqlStatement .= "LEFT JOIN jn_vehicle_permit ON jn_vehicle_permit.permit_id = permit.permit_id ";
            $sqlStatement .= "LEFT JOIN vehicle ON vehicle.vehicle_id = jn_vehicle_permit.vehicle_id ";
            $sqlStatement .= "WHERE applicant.applicant_id = {$applicantID};";
            if($this->debug) print("{$sqlStatement}<br/><br/>");                   
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the applicant record and associated data. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the applicant record and associated data.");
            }
        }
    }
    
    /**
     * Delete the data that is associated with the specified permit record.
     * 
     * The data that will be deleted includes:
     *  + The permit record
     *  + Notes made against the permit
     *  + Transaction log for the permit
     *  + Vehicle data associated with the permit
     * 
     * @param integer $permitID The database record ID for the permit.
     * @throws \Exception If the data cannot be deleted from the database.
     * @since 1.2.0
     * @see \CarParkingSystem\ParkingApplication::deletePermitRecord() ParkingApplication::deletePermitRecord()
     */
    public function deletePermitData($permitID) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement and query the database
            $sqlStatement = "DELETE permit, permit_note, permit_transaction_log, jn_vehicle_permit, vehicle FROM permit ";
            $sqlStatement .= "LEFT JOIN permit_note ON permit_note.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN permit_transaction_log ON permit_transaction_log.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN jn_vehicle_permit ON jn_vehicle_permit.permit_id = permit.permit_id ";
            $sqlStatement .= "INNER JOIN vehicle ON vehicle.vehicle_id = jn_vehicle_permit.vehicle_id ";
            $sqlStatement .= "WHERE permit.permit_id = {$permitID};";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            $appDatabase->queryDatabase($sqlStatement);
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to delete the permit record and associated data. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to delete the permit record and associated data.");
            }
        }
    }
    
    
    
    /* 
     * *******************************
     * * Methods to generate reports *
     * *******************************
     */
    
    /**
     * Get permit status data that can be used to generate a report.
     * 
     * @param string $dateFrom The date to search from, in the format `YYYY-MM-DD`.
     * @param string $dateTo The date to search until, in the format `YYYY-MM-DD`.
     * @param string $permitStatusCode The permit status code.
     * @param string $permitTypeCode The permit type code.
     * @return array|null Returns a two dimensional array of permit status data, if present; otherwise returns `NULL`.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.2.0
     */
    public function getReportDataPermitStatus($dateFrom, $dateTo, $permitStatusCode, $permitTypeCode) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT permit.permit_id, permit_type.permit_code, permit_type.description AS permit_type_description, permit.permit_serial_no, permit.status ";
            $sqlStatement .= "FROM permit LEFT JOIN permit_type ON permit_type.permit_type_id = permit.permit_type ";
            $sqlStatement .= "WHERE (CAST(permit.start_date AS DATE) BETWEEN CAST('{$dateFrom}' AS DATE) AND CAST('{$dateTo}' AS DATE)) AND ";
            $sqlStatement .= "permit.status = '{$permitStatusCode}' AND permit_type.permit_code = '{$permitTypeCode}';";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
        
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the report data on permit statuses. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the report data on permit statuses.");
            }
        }
    }
    
    
    
    /* 
     * *******************************************
     * * Methods to generate email notifications *
     * *******************************************
     */
    
    /**
     * Generate an email notification informing the applicant that their application has been submitted.
     * 
     * @param string $applicantTitle The applicant's title.
     * @param string $applicantFirstName The applicant's first name.
     * @param string $applicantSurname The applicant's surname.
     * @param string $applicantEmailAddress The applicant's email address.
     * @param string $permitType The permit type ID.
     * @param string $permitValidFrom The date the permit is valid from in the format `YYYY-MM-DD`.
     * @param string $permitValidTo The date the permit is valid until in the format `YYYY-MM-DD`.
     * @param string $vehicle1VRM The Vehicle Registration Mark of vehicle 1.
     * @param string $vehicle2VRM The Vehicle Registration Mark of vehicle 2, if present.
     * @return boolean Returns `TRUE`.
     * @since 1.0.0
     */
    public function generateEmailApplicationSubmitted($applicantTitle, $applicantFirstName, $applicantSurname, $applicantEmailAddress, $permitType, $permitValidFrom, $permitValidTo, $vehicle1VRM, $vehicle2VRM = NULL) {
                
        // Create the Plain Text part
        $notificationEmailMessageBodyFormatPlain = "Dear {$applicantTitle} {$applicantSurname},\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Thank you for submitting your application to join the University's ";
        $notificationEmailMessageBodyFormatPlain .= "Car Parking Scheme. Your car registration details will now be stored ";
        $notificationEmailMessageBodyFormatPlain .= "in the parking system, allowing you to park at any of the University's ";
        $notificationEmailMessageBodyFormatPlain .= "car parks at a reduced cost. Motorcycle parking is free. \r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Students scheme members living in university accommodation are eligible ";
        $notificationEmailMessageBodyFormatPlain .= "to free parking at their contracted accommodation only. You will ";
        $notificationEmailMessageBodyFormatPlain .= "shortly receive a confirmation email.\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "You can pay for parking by downloading the JustPark app on the iTunes \r\n";
        $notificationEmailMessageBodyFormatPlain .= "or Google Play Store or via www.justpark.com.  Alternatively you can \r\n";
        $notificationEmailMessageBodyFormatPlain .= "pay by card at one of the contactless parking terminals located on \r\n";
        $notificationEmailMessageBodyFormatPlain .= "City Campus (rear of the Gateway building) or at St. Peter's (southern \r\n";
        $notificationEmailMessageBodyFormatPlain .= "entrance to Reg Vardy building). Visitors to the National Glass \r\n";
        $notificationEmailMessageBodyFormatPlain .= "Centre can pay for parking at a terminal in front of the NGC. You \r\n";
        $notificationEmailMessageBodyFormatPlain .= "should note that vehicles registered on the University Car Parking \r\n";
        $notificationEmailMessageBodyFormatPlain .= "Scheme are not permitted to park at the NGC.\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Yours faithfully,\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Parking Services\r\n";
        $notificationEmailMessageBodyFormatPlain .= "(0191) 515 3366 | parkingservices.sunderland.ac.uk\r\n";
        
        // Create the HTML part
        $notificationEmailMessageBodyFormatHTML = "<style>
            p { margin: 0 0 0 10px; }
            hr {
                color: #d9d9d9;
                background-color: #d9d9d9;
                height: 1px;
                border: none;
            }
            body, table.body, h1, h2, h3, h4, h5, h6, p, td {
                color: #222222;
                font-family: \"Helvetica\", \"Arial\", sans-serif;
                font-weight: normal;
                padding:0;
                margin: 0;
                text-align: left;
                line-height: 1.3;
            }
            body, p {
                font-size: 14px;
                line-height:19px;
            }
            p { margin-bottom: 10px; }
            a {
                color: #f5903f;
                text-decoration: none;
            }
            a:hover { color: #ea6f0c !important; }
            a:active { color: #ea6f0c !important; }
            a:visited { color: #ea6f0c !important; }
        </style>
        <p>Dear {$applicantTitle} {$applicantSurname}</p>
        <p><strong>Thank you for submitting your application to join the University's Car Parking Scheme.</strong></p>
        <p>Your car registration details will now be stored in the parking system, allowing you to park at any of the 
        University's car parks at a reduced cost. Motorcycle parking is free.</p>
        <p>Students scheme members living in university accommodation are eligible to free parking at their contracted 
        accommodation only. You will shortly receive a confirmation email.</p>
        <p>You can pay for parking by downloading the JustPark app on the iTunes or Google Play store, or via 
        <a href=\"https://www.justpark.com/\"><strong>www.justpark.com</strong></a>. Alternatively you can pay by card 
        at one of the contactless parking terminals located on City Campus (rear of the Gateway building) or at St. 
        Peter's (southern entrance to Reg Vardy building). Visitors to the National Glass Centre can pay for parking 
        at a terminal in front of the NGC. You should note that vehicles registered on the University Car Parking 
        Scheme are not permitted to park at the NGC.</p>
        <hr />
        <p><strong>Parking Services</strong></p>
        <p><a href=\"tel:+441915153366\">(0191) 515 3366</a> | 
        <a href=\"mailto:parkingservices@sunderland.ac.uk\">parkingservices@sunderland.ac.uk</a></p>
        <p>For other information please 
        <a href=\"http://services.sunderland.ac.uk/facilities/carparking/\">visit our website</a>.</p>
        <p>(c) <a href=\"http://www.sunderland.ac.uk\">University of Sunderland</a>.</p>";

        // Init mailer class
        $mail = new \UoSMail(true);

        // Set email parameters
        $mail->addAddress($applicantEmailAddress, $applicantFirstName.' '.$applicantSurname);
        $mail->Subject = 'Registration Application Submitted';
        $mail->msgHTML($notificationEmailMessageBodyFormatHTML);
        $mail->AltBody = $notificationEmailMessageBodyFormatPlain;

        // Send the email
        $mail->send();
        
        return TRUE;
    }
    
    /**
     * Generate an email notification informing the applicant that their application has been approved.
     * 
     * @param string $applicantTitle The applicant's title.
     * @param string $applicantFirstName The applicant's first name.
     * @param string $applicantSurname The applicant's surname.
     * @param string $applicantEmailAddress The applicant's email address.
     * @param string $permitType The permit type ID.
     * @param string $permitValidFrom The date the permit is valid from in the format `YYYY-MM-DD`.
     * @param string $permitValidTo The date the permit is valid until in the format `YYYY-MM-DD`.
     * @param string $vehicle1VRM The Vehicle Registration Mark of vehicle 1.
     * @param string $vehicle2VRM The Vehicle Registration Mark of vehicle 2, if present.
     * @return boolean Returns `TRUE`.
     * @since 1.0.0
     */
    public function generateEmailApplicationApproved($applicantTitle, $applicantFirstName, $applicantSurname, $applicantEmailAddress, $permitType, $permitValidFrom, $permitValidTo, $vehicle1VRM, $vehicle2VRM = NULL) {

        // Create the Plain Text part
        $notificationEmailMessageBodyFormatPlain = "Dear {$applicantTitle} {$applicantSurname},\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Your application to join the University's Car Parking Scheme has been ";
        $notificationEmailMessageBodyFormatPlain .= "approved. Your car registration details will now be stored in the ";
        $notificationEmailMessageBodyFormatPlain .= "parking system, allowing you to park at any of the University's car ";
        $notificationEmailMessageBodyFormatPlain .= "parks at a reduced cost. Motorcycle parking is free.\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Students scheme members living in university accommodation are ";
        $notificationEmailMessageBodyFormatPlain .= "eligible to free parking at their contracted accommodation only.\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "You can pay for parking by downloading the JustPark app on the iTunes ";
        $notificationEmailMessageBodyFormatPlain .= "or Google Play Store or via www.justpark.com. Alternatively you can ";
        $notificationEmailMessageBodyFormatPlain .= "pay by card at one of the contactless parking terminals located on ";
        $notificationEmailMessageBodyFormatPlain .= "City Campus (rear of the Gateway building) or at St. Peter's ";
        $notificationEmailMessageBodyFormatPlain .= "(southern entrance to Reg Vardy building). Visitors to the National ";
        $notificationEmailMessageBodyFormatPlain .= "Glass Centre can pay for parking at a terminal in front of the NGC. ";
        $notificationEmailMessageBodyFormatPlain .= "You should note that vehicles registered on the University Car ";
        $notificationEmailMessageBodyFormatPlain .= "Parking Scheme are not permitted to park at the NGC. \r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Name: {$applicantTitle} {$applicantFirstName} {$applicantSurname}\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Email: {$applicantEmailAddress}\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Permit Type: {$permitType}\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Vehicle 1: " . strtoupper($vehicle1VRM) . "\r\n";
        if ($vehicle2VRM != NULL) { $notificationEmailMessageBodyFormatPlain .= "Vehicle 2: " . strtoupper($vehicle2VRM) . "\r\n"; }
        $notificationEmailMessageBodyFormatPlain .= "Valid From: " . date('d/m/Y', strtotime($permitValidFrom)) . "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Valid Until: " . date('d/m/Y', strtotime($permitValidTo)) . "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Yours faithfully,\r\n";
        $notificationEmailMessageBodyFormatPlain .= "Parking Services\r\n";
        $notificationEmailMessageBodyFormatPlain .= "(0191) 515 3366 | parkingservices.sunderland.ac.uk\r\n";
        
        // Create the HTML part
        $notificationEmailMessageBodyFormatHTML = "<style>
            p { margin: 0 0 0 10px; }
            hr {
                color: #d9d9d9;
                background-color: #d9d9d9;
                height: 1px;
                border: none;
            }
            body, p, li {
                color: #222222;
                font-family: \"Helvetica\", \"Arial\", sans-serif;
                font-weight: normal;
                padding:0;
                margin: 0;
                text-align: left;
                line-height: 1.3;
            }
            body, p {
                font-size: 14px;
                line-height:19px;
            }
            p { margin-bottom: 10px; }
            a {
                color: #f5903f;
                text-decoration: none;
            }
            a:hover { color: #ea6f0c !important; }
            a:active { color: #ea6f0c !important; }
            a:visited { color: #ea6f0c !important; }
        </style>
        <p>Dear {$applicantTitle} {$applicantSurname}</p>
        <p><strong>Your application to join the University's Car Parking Scheme has been approved.</strong></p>
        <p>Your car registration details will now be stored in the parking system, allowing you to park at any of 
        the University's car parks at a reduced cost. Motorcycle parking is free.</p>
        <p>Students scheme members living in university accommodation are eligible to free parking at their 
        contracted accommodation only.</p>
        <p>You can pay for parking by downloading the JustPark app on the iTunes or Google Play Store or via 
        <a href=\"https://www.justpark.com/\"><strong>www.justpark.com</strong></a>. Alternatively you can pay by 
        card at one of the contactless parking terminals located on City Campus (rear of the Gateway building) or 
        at St. Peter's (southern entrance to Reg Vardy building). Visitors to the National Glass Centre can pay for 
        parking at a terminal in front of the NGC. You should note that vehicles registered on the University Car 
        Parking Scheme are not permitted to park at the NGC.</p>
        <ul>
        <li>Name: <strong>{$applicantTitle} {$applicantFirstName} {$applicantSurname}</strong></li>
        <li>Email: <strong>{$applicantEmailAddress}</strong></li>
        <li>Permit Type: <strong>{$permitType}</strong></li>
        <li>Vehicle 1: <strong>".strtoupper($vehicle1VRM)."</strong></li>";
    if ($vehicle2VRM != NULL) $notificationEmailMessageBodyFormatHTML .= "<li>Vehicle 2: <strong>".strtoupper($vehicle2VRM)."</strong></li>";
    $notificationEmailMessageBodyFormatHTML .= "<li>Valid From: <strong>".date('d/m/Y', strtotime($permitValidFrom))."</strong></li>
        <li>Valid To: <strong>".date('d/m/Y', strtotime($permitValidTo))."</strong></li>
        </ul>
        <hr />
        <p><strong>Parking Services</strong></p>
        <p><a href=\"tel:+441915153366\">(0191) 515 3366</a> | 
        <a href=\"mailto:parkingservices@sunderland.ac.uk\">parkingservices@sunderland.ac.uk</a></p>
        <p>For other information please 
        <a href=\"http://services.sunderland.ac.uk/facilities/carparking/\">visit our website</a>.</p>
        <p>(c) <a href=\"http://www.sunderland.ac.uk\">University of Sunderland</a>.</p>";
        
        // Init mailer class
        $mail = new \UoSMail(true);

        // Set email parameters
        $mail->addAddress($applicantEmailAddress, $applicantFirstName.' '.$applicantSurname);
        $mail->Subject = 'Registration Application Approved';
        $mail->msgHTML($notificationEmailMessageBodyFormatHTML);
        $mail->AltBody = $notificationEmailMessageBodyFormatPlain;

        // Send the email
        $mail->send();
       
        return TRUE;
    }
    
    
    /**
     * Get a list of potential duplicate applicant records.
     *
     * @param integer $applicantID The applicant ID.
     * @param integer $idNum The applicant's staff/student number.
     * @param integer $firstName The applicant's first name.
     * @param integer $surname The applicant's surname.
     * @param integer $emailAddr The applicant's email address.
     * 
     * @return array|null Returns an array of applicants if any records are found; otherwise returns `NULL`.
     * @throws \Exception If the data cannot be retrieved from the database.
     * @since 1.0.0
     */
    public function getApplicantDuplicates($applicantID, $idNum, $firstName, $surname, $emailAddr) {
        // Connect to the database
        $appDatabase = new \CarParkingSystem\AppDatabase();
        
        try {
            // Create the SQL statement
            $sqlStatement = "SELECT a.applicant_id, i.user_id, i.id_num, TRIM(CONCAT(a.title, ' ', a.first_name, ' ', a.surname)) AS full_name, a.house_flat_property, a.address_1, a.address_2, ";
            $sqlStatement .= "a.address_3, a.post_town, a.postcode, a.email_addr, (select count(*) from permit AS p where p.applicant_id = a.applicant_id and p.status IN ('RI','IS')) as active_permits, ";
            $sqlStatement .= "a.dt_create, a.dt_modify FROM applicant AS a INNER JOIN applicant_add_info AS i ON a.applicant_id = i.applicant_id WHERE ((a.first_name = '{$firstName}' AND ";
            $sqlStatement .= "a.surname = '{$surname}') OR (i.id_num != '' AND i.id_num = '{$idNum}') OR (a.email_addr != '' AND a.email_addr = '{$emailAddr}')) AND a.applicant_id != {$applicantID}";
            if($this->debug) print("{$sqlStatement}<br/><br/>");  
            // Query the database
            if ($sqlResult = $appDatabase->queryDatabase($sqlStatement)) {
                return $sqlResult;
            }
            
        } catch (\Exception $ex) {
            if ($this->debug) {
                throw new \Exception("Unable to retrieve the permits for the applicant. [ERROR]".$ex."[/ERROR] [SQL]".$sqlStatement."[/SQL]");
            } else {
                throw new \Exception("Unable to retrieve the permits for the applicant.");
            }
        }
    }
    

}

?>