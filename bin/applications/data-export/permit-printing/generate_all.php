<?php

// Include class file.
require_once(dirname(__FILE__) . "/../../../../lib/classes/application/ParkingApplication.php");


/**
 * Permit application data export - all permit types.
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
    $dataExportFile = "New_UoS_Permits_" . date('Ymd') . ".csv";
    
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
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "STF"))  ? $permitApplications1  = $parkingApplication->getPermitApplicationsByStatus("AP", "STF")  : $permitApplications1 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "STF"))  ? $permitApplications2  = $parkingApplication->getPermitApplicationsByStatus("RP", "STF")  : $permitApplications2 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "ANN"))  ? $permitApplications3  = $parkingApplication->getPermitApplicationsByStatus("AP", "ANN")  : $permitApplications3 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "ANN"))  ? $permitApplications4  = $parkingApplication->getPermitApplicationsByStatus("RP", "ANN")  : $permitApplications4 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "ACSF")) ? $permitApplications5  = $parkingApplication->getPermitApplicationsByStatus("AP", "ACSF") : $permitApplications5 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "ACSF")) ? $permitApplications6  = $parkingApplication->getPermitApplicationsByStatus("RP", "ACSF") : $permitApplications6 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "RESF")) ? $permitApplications7  = $parkingApplication->getPermitApplicationsByStatus("AP", "RESF") : $permitApplications7 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "RESF")) ? $permitApplications8  = $parkingApplication->getPermitApplicationsByStatus("RP", "RESF") : $permitApplications8 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "SDT"))  ? $permitApplications9  = $parkingApplication->getPermitApplicationsByStatus("AP", "SDT")  : $permitApplications9 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "SDT"))  ? $permitApplications10 = $parkingApplication->getPermitApplicationsByStatus("RP", "SDT")  : $permitApplications10 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "RES"))  ? $permitApplications11 = $parkingApplication->getPermitApplicationsByStatus("AP", "RES")  : $permitApplications11 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "RES"))  ? $permitApplications12 = $parkingApplication->getPermitApplicationsByStatus("RP", "RES")  : $permitApplications12 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "ACST")) ? $permitApplications13 = $parkingApplication->getPermitApplicationsByStatus("AP", "ACST") : $permitApplications13 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "ACST")) ? $permitApplications14 = $parkingApplication->getPermitApplicationsByStatus("RP", "ACST") : $permitApplications14 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("AP", "HOSP")) ? $permitApplications15 = $parkingApplication->getPermitApplicationsByStatus("AP", "HOSP") : $permitApplications15 = array();
        is_array($parkingApplication->getPermitApplicationsByStatus("RP", "HOSP")) ? $permitApplications16 = $parkingApplication->getPermitApplicationsByStatus("RP", "HOSP") : $permitApplications16 = array();

        // Merge all permit arrays
        $permitApplications = array_merge(
            $permitApplications1, 
            $permitApplications2, 
            $permitApplications3, 
            $permitApplications4, 
            $permitApplications5, 
            $permitApplications6, 
            $permitApplications7, 
            $permitApplications8, 
            $permitApplications9, 
            $permitApplications10, 
            $permitApplications11, 
            $permitApplications12, 
            $permitApplications13, 
            $permitApplications14, 
            $permitApplications15, 
            $permitApplications16);

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
					$permitNotesString = "";

                    // Extract the data and add it to the buffer.
					$dataBuffer .= "\"{$vehicleDetails[0]['registration']}\",\"{$permitApplication['start_date']}\",\"{$permitApplication['end_date']}\",\"{$permitApplication['first_name']}\",\"{$permitApplication['surname']}\",\"{$permitApplication['permit_type_description']}\",\"{$permitNotesString}\",\"\"";
					$dataBuffer .= "\n";
                    
                    // Check if thare are the any secondary vehicles on the permit.
                    if (isset($vehicleDetails[1]['registration'])) {
						$dataBuffer .= "\"{$vehicleDetails[1]['registration']}\",\"{$permitApplication['start_date']}\",\"{$permitApplication['end_date']}\",\"{$permitApplication['first_name']}\",\"{$permitApplication['surname']}\",\"{$permitApplication['permit_type_description']}\",\"{$permitNotesString}\",\"\"";
                        $dataBuffer .= "\n";
                    }
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
            $fileHeader = "\"VRM\",\"Start Date\",\"End Date\",\"First Name\",\"Last Name\",\"Reference No.\",\"Notes\"";
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
        // Merge approved permit arrays
        $approvedPermitApplications = array_merge(
            $permitApplications1, 
            $permitApplications3, 
            $permitApplications5, 
            $permitApplications7, 
            $permitApplications9, 
            $permitApplications11, 
            $permitApplications13, 
            $permitApplications15);

        // Check if there were any matching permits.
        if (count($approvedPermitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($approvedPermitApplications as $permitApplication) {
                // ... but only if the print exclusion flag is set to "No".
                if ($permitApplication['print_exclude'] == 'n') {
                    // Set the status flag.
                    $parkingApplication->setPermitStatus($permitApplication['permit_id'], "IS");
                    $parkingApplication->createPermitTransactionLogEntry($permitApplication['permit_id'], "Permit data exported for First Parking", "System");
                    $parkingApplication->createPermitTransactionLogEntry($permitApplication['permit_id'], "Permit status changed: '{$parkingApplication->getPermitStatus('AP')}' to '{$parkingApplication->getPermitStatus('IS')}'", "System");
                }
            }
        }

        // Merge issued permit arrays
        $issuedPermitApplications = array_merge(
            $permitApplications2, 
            $permitApplications4, 
            $permitApplications6, 
            $permitApplications8, 
            $permitApplications10, 
            $permitApplications12, 
            $permitApplications14, 
            $permitApplications16);

        if (count($issuedPermitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($issuedPermitApplications as $permitApplication) {
                // ... but only if the print exclusion flag is set to "No".
                if ($permitApplication['print_exclude'] == 'n') {
                    // Set the status flag.
                    $parkingApplication->setPermitStatus($permitApplication['permit_id'], "RI");
                    $parkingApplication->createPermitTransactionLogEntry($permitApplication['permit_id'], "Permit data exported for First Parking", "System");
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
