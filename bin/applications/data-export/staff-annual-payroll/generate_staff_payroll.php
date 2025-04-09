<?php

// Include class file.
require_once(dirname(__FILE__) . "/../../../../lib/classes/application/ParkingApplication.php");


/**
 * Staff Annual Permits - Data for Payroll.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.4.0
 */


/*
 * Override the default PHP configuration settings due to the large dataset that may be returned by the API.
 */
// Maximum script execution time.
ini_set("max_execution_time", 120);

// Memory limit.
ini_set("memory_limit", "1024M");


try {
    /*
     * Setup environment.
     */
    // Define data export properties.
    $dataExportPath = dirname(__FILE__) . "/../../../../data/generated/staff-annual-payroll";
    $dataExportFile = "staff-payroll_" . date('Ymd') . ".csv";
    
    // Define the data export search timeframe.
    $dataExportSearchTimeframe = "1 month ago";
    
    // Clear the contents buffer.
    $dataBuffer = NULL;
    
    
    
    /*
     * Permit data retrieval.
     */
    try {
        // Initialise the API and retrieve the data.
        $parkingApplication = new \CarParkingSystem\ParkingApplication();
        
        // Get the permit data for the specified statuses.
        $issuedPermitData = $parkingApplication->getPermitApplicationsByStatus("IS", "ANN");
        $reissuedPermitData = $parkingApplication->getPermitApplicationsByStatus("RI", "ANN");
        
        // Check that permit data has been returned for each of the statuses.
        $issuedPermits = ((is_array($issuedPermitData)) ? $issuedPermitData : array());
        $reissuedPermits = ((is_array($reissuedPermitData)) ? $reissuedPermitData : array());
        
        // Merge the permit datasets.
        $permitApplications = array_merge($issuedPermits, $reissuedPermits);
        
        // Sort the merged permit dataset by permit ID.
        array_multisort(array_map(function($permitApplicationEntry) {
            return $permitApplicationEntry['permit_id'];
        }, $permitApplications), SORT_ASC, SORT_REGULAR, $permitApplications);
        
        
        // Check if there are any matching permits.
        if (count($permitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($permitApplications as $permitApplication) {
                // ... but only if they were applied for since the last data export was generated.
                if (strtotime($permitApplication['dt_create']) >= strtotime($dataExportSearchTimeframe)) {
                    // Get the applicant's details.
                    $applicantDetails = $parkingApplication->getApplicantDetails($permitApplication['applicant_id']);
                    $applicantExtraDetails = $parkingApplication->getApplicantExtraDetails($permitApplication['applicant_id']);

                    // Extract the data and add it to the buffer.
                    $dataBuffer .= "\"{$applicantExtraDetails['id_num']}\",\"{$applicantExtraDetails['staff_auth_payroll']}\",\"{$applicantExtraDetails['attendance']}\",";
                    $dataBuffer .= "\"{$permitApplication['permit_serial_no']}\",\"{$permitApplication['start_date']}\",\"{$permitApplication['end_date']}\",";
                    $dataBuffer .= "\"{$applicantDetails['title']}\",\"{$applicantDetails['first_name']}\",\"{$applicantDetails['surname']}\",\"{$applicantDetails['house_flat_property']}\",\"{$applicantDetails['address_1']}\",\"{$applicantDetails['address_2']}\",\"{$applicantDetails['address_3']}\",\"{$applicantDetails['post_town']}\",\"{$applicantDetails['county']}\",\"{$applicantDetails['postcode']}\",\"{$applicantDetails['telephone']}\",\"{$applicantDetails['email_addr']}\"";
                    $dataBuffer .= "\n";
                }
            }
        }
    
    } catch (\Exception $ex) {
        throw new Exception("Unable to retireve the parking permit data. {$ex}");
    }
    
    
    
    /*
     * Permit data export.
     */
    try {
        // Construct file header.
        $fileHeader = "\"Staff Num\",\"Auth to Setup Mandate\",\"Full/Part Time\",";
        $fileHeader .= "\"Permit Serial Num\",\"Permit Start Date\",\"Permit End Date\",";
        $fileHeader .= "\"Applicant Title\",\"Applicant First Name\",\"Applicant Surname\",\"Applicant House/Flat No/Name\",\"Applicant Address 1\",\"Applicant Address 2\",\"Applicant Address 3\",\"Applicant Post Town\",\"Applicant County\",\"Applicant Postcode\",\"Applicant Telephone Num\",\"Applicant Email Address\"";
        $fileHeader .= "\n";

        // Concatenate the file header and data buffer.
        $fileData = $fileHeader . $dataBuffer;

        // Write to the file.
        $fileHandle = fopen("{$dataExportPath}/{$dataExportFile}", "w");
        fwrite($fileHandle, $fileData);
        fclose($fileHandle);
    
    } catch (\Exception $ex) {
        throw new Exception("Unable to create the staff data file for Payroll. {$ex}");
    }
    
    
    
    /*
     * Permit transaction log update.
     */
    try {
        // Check if there are any matching permits.
        if (count($permitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($permitApplications as $permitApplication) {
                // ... and add an entry to the permit's transaction log, but only if it was applied for since the last data
                // export was generated.
                if (strtotime($permitApplication['dt_create']) >= strtotime($dataExportSearchTimeframe)) {
                    $currentPeriod = date("F Y");
                    $parkingApplication->createPermitTransactionLogEntry($permitApplication['permit_id'], "Permit data exported for payroll in the '{$currentPeriod}' extract", "System");
                }
            }
        }
    
    } catch (\Exception $ex) {
        throw new Exception("Unable to update the parking permit transaction logs. {$ex}");
    }

} catch (\Exception $ex) {
    die($ex);
}

?>
