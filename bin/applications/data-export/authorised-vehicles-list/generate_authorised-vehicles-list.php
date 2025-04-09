<?php

// Include class file.
require_once(dirname(__FILE__) . "/../../../../lib/classes/application/ParkingApplication.php");


/**
 * Authorised vehicles list data export.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2017 University of Sunderland
 * @license Proprietary
 * @version 1.1.0
 */


/*
 * Override the default PHP configuration settings due to the large dataset that may be returned by the API.
 */
// Maximum script execution time.
ini_set("max_execution_time", 300);

// Memory limit.
ini_set("memory_limit", "1024M");


try {
    /*
     * Setup environment.
     */
    // Define data export properties.
    $dataExportPath = dirname(__FILE__) . "/../../../../data/generated/authorised-vehicles-list";
    $dataExportFile = "authorised-vehicles-list_" . date('Ymd') . ".csv";

    // Clear the contents buffer.
    $dataBuffer = NULL;
    
    
    /*
     * Permit data retrieval.
     */
    try {
        // Initialise the API and retrieve the data.
        $parkingApplication = new \CarParkingSystem\ParkingApplication();
        
        // Get the permit data for the specified statuses.
        // "Approved, pending printing" and "Reissued, pending printing" should have already been processed by now, but they
        // have been added to trap any strays and ensure that they are included.
        $issuedPermitData = $parkingApplication->getPermitApplicationsByStatus("IS");
        $reissuedPermitData = $parkingApplication->getPermitApplicationsByStatus("RI");
        $approvedPendingPermitData = $parkingApplication->getPermitApplicationsByStatus("AP");
        $reissuedPendingPermitData = $parkingApplication->getPermitApplicationsByStatus("RP");
        
        // Check that permit data has been returned for each of the statuses.
        $issuedPermits = ((is_array($issuedPermitData)) ? $issuedPermitData : array());
        $reissuedPermits = ((is_array($reissuedPermitData)) ? $reissuedPermitData : array());
        $approvedPendingPermits = ((is_array($approvedPendingPermitData)) ? $approvedPendingPermitData : array());
        $reissuedPendingPermits = ((is_array($reissuedPendingPermitData)) ? $reissuedPendingPermitData : array());
        
        // Merge the permit datasets.
        $permitApplications = array_merge($issuedPermits, $reissuedPermits, $approvedPendingPermits, $reissuedPendingPermits);
		//$permitApplications = $approvedPendingPermits;
        
        // Sort the merged permit dataset by permit start date.
        array_multisort(array_map(function($permitApplicationEntry) {
            return $permitApplicationEntry['start_date'];
        }, $permitApplications), SORT_ASC, SORT_REGULAR, $permitApplications);
        
        // Check if there are any matching permits.
        if (count($permitApplications) > 0) {
            // Get all of the matching permits ...
            foreach ($permitApplications as $permitApplication) {
                // ... but only if the print exclusion flag is set to "No".
                if ($permitApplication['print_exclude'] == 'n') {
                    // Get additional data for the permit.
                    $vehicleDetails = $parkingApplication->getPermitVehicles($permitApplication['permit_id']);
					$permitNotes = $parkingApplication->getPermitNotes($permitApplication['permit_id']);
					//if(is_array($permitNotes)) {
					//	$permitNotesString = implode("; ",$permitNotes);
					//} else {
						$permitNotesString = "";
					//}

                    // Extract the data and add it to the buffer.
                    //$dataBuffer .= "\"{$vehicleDetails[0]['registration']}\",\"{$permitApplication['end_date']}\",\"{$permitApplication['permit_type_description']}\",\"{$permitApplication['start_date']}\",\"\",\"\"";
					$dataBuffer .= "\"{$vehicleDetails[0]['registration']}\",\"{$permitApplication['start_date']}\",\"{$permitApplication['end_date']}\",\"{$permitApplication['first_name']}\",\"{$permitApplication['surname']}\",\"{$permitApplication['permit_type_description']}\",\"{$permitNotesString}\",\"\"";
					$dataBuffer .= "\n";
                    
                    // Check if thare are the any secondary vehicles on the permit.
                    if (isset($vehicleDetails[1]['registration'])) {
                        // There is a secondary vehicle so add the details to it's own row in the buffer.
                        //$dataBuffer .= "\"{$vehicleDetails[1]['registration']}\",\"{$permitApplication['end_date']}\",\"{$permitApplication['permit_type_description']}\",\"{$permitApplication['start_date']}\",\"\",\"\"";
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
        // Construct file header.
        $fileHeader = "\"VRM\",\"Start Date\",\"End Date\",\"First Name\",\"Last Name\",\"Reference No.\",\"Notes\"";
        $fileHeader .= "\n";

        // Concatenate the file header and data buffer.
        $fileData = $fileHeader . $dataBuffer;

        // Write to the file.
        $fileHandle = fopen("{$dataExportPath}/{$dataExportFile}", "w");
        fwrite($fileHandle, $fileData);
        fclose($fileHandle);
    
    } catch (\Exception $ex) {
        throw new Exception("Unable to create the parking permit data file. {$ex}");
    }

} catch (\Exception $ex) {
    die($ex);
}

?>
