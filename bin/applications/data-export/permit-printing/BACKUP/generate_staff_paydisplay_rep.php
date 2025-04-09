<?php

// Include class file.
require_once(dirname(__FILE__) . "/../../../../lib/classes/application/ParkingApplication.php");


/**
 * Permit application data export - Staff Pay & Display (Replacement).
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.1.0
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
    $dataExportPath = dirname(__FILE__) . "/../../../../data/generated/permit-printing";
    $dataExportFile = "STF_RI_" . date('Ymd') . ".csv";
    
    // Set a flag to indicate if there is data in the export.
    $dataInExport = FALSE;

    // Clear the contents buffer.
    $dataBuffer = NULL;
    
    
    
    /*
     * Permit data retrieval.
     */
    try {
        // Initialise the API and retrieve the data.
        $parkingApplication = new \CarParkingSystem\ParkingApplication();
        $permitApplications = $parkingApplication->getPermitApplicationsByStatus("RP", "STF");
        
        // Check if there are any matching permits.
        if (count($permitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($permitApplications as $permitApplication) {
                // ... but only if the print exclusion flag is set to "No".
                if ($permitApplication['print_exclude'] == 'n') {
                    // Update data export flag.
                    $dataInExport = TRUE;
                    
                    // Get additional data for the permit.
                    $vehicleDetails = $parkingApplication->getPermitVehicles($permitApplication['permit_id']);
                    $applicantDetails = $parkingApplication->getApplicantDetails($permitApplication['applicant_id']);

                    // Extract the data and add it to the buffer.
                    $dataBuffer .= "\"{$permitApplication['permit_id']}\",\"{$permitApplication['permit_type_code']}\",\"{$permitApplication['permit_type_description']}\",\"{$permitApplication['permit_serial_no']}\",\"{$permitApplication['start_date']}\",\"{$permitApplication['end_date']}\",";
                    $dataBuffer .= "\"{$vehicleDetails[0]['registration']}\",\"";
                    $dataBuffer .= (isset($vehicleDetails[1]['registration'])) ? $vehicleDetails[1]['registration'] : "";
                    $dataBuffer .= "\",\"{$applicantDetails['title']}\",\"{$applicantDetails['first_name']}\",\"{$applicantDetails['surname']}\",\"{$applicantDetails['house_flat_property']}\",\"{$applicantDetails['address_1']}\",\"{$applicantDetails['address_2']}\",\"{$applicantDetails['address_3']}\",\"{$applicantDetails['post_town']}\",\"{$applicantDetails['county']}\",\"{$applicantDetails['postcode']}\",\"{$applicantDetails['telephone']}\",\"{$applicantDetails['email_addr']}\"";
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
        // Check if there were any matching permits and there is data in the export.
        if (count($permitApplications) > 0 && $dataInExport == TRUE) {
            // Construct file header.
            $fileHeader = "\"Permit Record ID\",\"Permit Type\",\"Permit Description\",\"Permit Serial Num\",\"Permit Start Date\",\"Permit End Date\",";
            $fileHeader .= "\"Permit VRM 1\",\"Permit VRM 2\",";
            $fileHeader .= "\"Applicant Title\",\"Applicant First Name\",\"Applicant Surname\",\"Applicant House/Flat No/Name\",\"Applicant Address 1\",\"Applicant Address 2\",\"Applicant Address 3\",\"Applicant Post Town\",\"Applicant County\",\"Applicant Postcode\",\"Applicant Telephone Num\",\"Applicant Email Address\"";
            $fileHeader .= "\n";

            // Concatenate the file header and data buffer.
            $fileData = $fileHeader . $dataBuffer;

            // Write to the file.
            $fileHandle = fopen("{$dataExportPath}/{$dataExportFile}", "w");
            fwrite($fileHandle, $fileData);
            fclose($fileHandle);
        }
    
    } catch (\Exception $ex) {
        throw new Exception("Unable to create the parking permit data file. {$ex}");
    }
    
    
    
    /*
     * Permit status update.
     */
    try {
        // Check if there were any matching permits.
        if (count($permitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($permitApplications as $permitApplication) {
                // ... but only if the print exclusion flag is set to "No".
                if ($permitApplication['print_exclude'] == 'n') {
                    // Set the status flag.
                    $parkingApplication->setPermitStatus($permitApplication['permit_id'], "RI");
                    $parkingApplication->createPermitTransactionLogEntry($permitApplication['permit_id'], "Permit data exported for printing", "System");
                    $parkingApplication->createPermitTransactionLogEntry($permitApplication['permit_id'], "Permit status changed: '{$parkingApplication->getPermitStatus('RP')}' to '{$parkingApplication->getPermitStatus('RI')}'", "System");
                }
            }
        }
    
    } catch (\Exception $ex) {
        throw new Exception("Unable to update the parking permit statuses. {$ex}");
    }

} catch (\Exception $ex) {
    die($ex);
}

?>
