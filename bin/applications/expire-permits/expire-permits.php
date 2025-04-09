<?php

// Include class files
require_once(dirname(__FILE__) . "/../../../lib/classes/application/ParkingApplication.php");
require_once(dirname(__FILE__) . "/../../../lib/classes/application/ParkingAPI.php");


/**
 * Marks expired permits with a status of 'Expired'.
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
ini_set("max_execution_time", 300);

// Memory limit.
ini_set("memory_limit", "1024M");


try {
    /*
     * Permit data retrieval and set status.
     */
    try {
        // Initialise the API and retrieve the data.
        $parkingApplication = new \CarParkingSystem\ParkingApplication();
        $parkingAPI = new \UoSParkingApi\zatpark();
        
        // Get the permit data for the specified statuses.
        $rejectedPermitData = $parkingApplication->getPermitApplicationsByStatus("RJ");
        $heldPermitData = $parkingApplication->getPermitApplicationsByStatus("HD");
        $issuedPermitData = $parkingApplication->getPermitApplicationsByStatus("IS");
        $reissuedPermitData = $parkingApplication->getPermitApplicationsByStatus("RI");
        $cancelledPermitData = $parkingApplication->getPermitApplicationsByStatus("CN");
        
        // Check that permit data has been returned for each of the statuses.
        $rejectedPermits = ((is_array($rejectedPermitData)) ? $rejectedPermitData : array());
        $heldPermits = ((is_array($heldPermitData)) ? $heldPermitData : array());
        $issuedPermits = ((is_array($issuedPermitData)) ? $issuedPermitData : array());
        $reissuedPermits = ((is_array($reissuedPermitData)) ? $reissuedPermitData : array());
        $cancelledPermits = ((is_array($cancelledPermitData)) ? $cancelledPermitData : array());
        
        // Merge the permit datasets.
        $permitsToExpire = array_merge($rejectedPermits, $heldPermits, $issuedPermits, $reissuedPermits, $cancelledPermits);

        // Sort the merged permit dataset by permit ID.
        array_multisort(array_map(function($permitExpiryEntry) {
            return $permitExpiryEntry['permit_id'];
        }, $permitsToExpire), SORT_ASC, SORT_REGULAR, $permitsToExpire);


        // Compile a list of active vehicles for the current year (as we do not want to cancel these via the API)
        $activeVehicles = array();
        $activePermits = array_merge($issuedPermits, $reissuedPermits);
        foreach ($activePermits as $activePermit) {
            if (strtotime($activePermit['end_date']) > strtotime("31st August this year")) {
                
                // Strip spaces from registrations
                $vehicleRegistrations = str_replace(' ', '', $activePermit['vehicle_registrations']);

                foreach(explode(',',$vehicleRegistrations) as $vehicleRegistration) {
                    if(!in_array($vehicleRegistration, $activeVehicles)) {
                        // Vehicle still active to append to array
                        $activeVehicles[] = $vehicleRegistration;
                    }
                }
            }
        }


        // Check if there are any matching permits.
        if (count($permitsToExpire) > 0) {
            // Get all of the matching permits ...
            foreach ($permitsToExpire as $permitToExpire) {
                // ... but only if they have expired this year.
                if (strtotime($permitToExpire['end_date']) <= strtotime("31st August this year")) {
                    
                    // Set the status flag.
                    $parkingApplication->setPermitStatus($permitToExpire['permit_id'], "EX");
                    $parkingApplication->createPermitTransactionLogEntry($permitToExpire['permit_id'], "Permit status changed: '{$parkingApplication->getPermitStatus($permitToExpire['status'])}' to '{$parkingApplication->getPermitStatus('EX')}'", "System");
                    $parkingApplication->createPermitTransactionLogEntry($permitToExpire['permit_id'], "Permit has expired so is now marked as such in the system", "System");

                    try {
                        // For each vehicle...
                        foreach(explode(',',$permitToExpire['vehicle_registrations']) as $vehicleRegistration) {

                            // Check the vehicle does not belong to a permit which has been issued for the new academic year
                            if(!in_array(str_replace(' ', '', $vehicleRegistration), $activeVehicles)) {

                                // Cancel the vehicle via the API
                                $apiResult = $parkingAPI->cancelPermit(
                                    $vehicleRegistration, 
                                    $parkingAPI->formatReferenceNo($permitToExpire['permit_serial_no'])
                                );

                                // Log the API result
                                $parkingApplication->createPermitTransactionLogEntry($permitToExpire['permit_id'], "{$apiResult['message']}", "System");
                            }
                        }

                    } catch (\Exception $ex2) {
                        $parkingApplication->createPermitTransactionLogEntry($permitToExpire['permit_id'], "There was a problem cancelling the permit for {$permitVehicle['registration']} via the API", "System");
                    }
                }
            }
        }    

    } catch (\Exception $ex) {
        throw new Exception("Unable to mark expired parking permits as 'Expired'. {$ex}");
    }

} catch (\Exception $ex3) {
    die($ex3);
}
?>