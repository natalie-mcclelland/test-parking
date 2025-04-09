<?php

// Include class file
require_once(dirname(__FILE__) . "/../../../lib/classes/application/ParkingApplication.php");


/**
 * Deletes stale applicant, permit, vehicle and associated data.
 * 
 * In order to maintain compliance with relevant legislation and prevent the system from retaining data that is no longer
 * required, this script will purge the data that is older than the specified data retention period, i.e. "stale" data.
 * This is achieved by using a four step process:
 *  + Step 1: Determine which Blue Badge image files can be deleted
 *  + Step 2: Delete the stale applicant and associated data
 *  + Step 3: Delete the remaining stale Blue Badge data
 *  + STep 4: Delete the remaining stale permits, vehicles and associated data
 * 
 * The order in which the steps are undertaken is important as the tasks need to be performed logically.  For example, the
 * list of Blue Badge images needs to be known before the data is deleted from the database.  Additionally, deleting stale
 * applicant and associated data first saves processing time when later deleting stale data in specific datasets.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2017 University of Sunderland
 * @license Proprietary
 * @version 1.0.0
 */


/*
 * Override the default PHP configuration settings due to the large dataset that may be returned by the API
 */
// Maximum script execution time
ini_set("max_execution_time", 300);

// Memory limit
ini_set("memory_limit", "1024M");


try {
    // Initialise the API and retrieve the data
    $parkingApplication = new \CarParkingSystem\ParkingApplication();
    
    
    /*
     * Step 1: Determine which Blue Badge image files can be deleted
     */
    try {
        // Retrieve the data
        $staleBlueBadgeRecords = $parkingApplication->getAllStaleBlueBadgeRecords();
        
        // Check if there are any records
        if (count($staleBlueBadgeRecords) > 0) {
            // Create a buffer for the data
            $staleBlueBadgeImages = NULL;
        
            // Get the image filenames for each record ...
            foreach ($staleBlueBadgeRecords as $staleBlueBadgeRecord) {
                // ... and add them to the list.
                $staleBlueBadgeImages .= $staleBlueBadgeRecord['scan_file_front'] . "\n";
                $staleBlueBadgeImages .= $staleBlueBadgeRecord['scan_file_back'] . "\n";
            }
            
            // Create the data file that will contain the list
            $blueBadgeDataFileName = __DIR__ . "/../../../data/generated/data-deletion/stale-blue-badge-images.txt";
            $blueBadgeDataFile = fopen($blueBadgeDataFileName, "w+");
            fwrite($blueBadgeDataFile, $staleBlueBadgeImages);
            fclose($blueBadgeDataFile);
        }
        
    
    } catch (\Exception $ex) {
        throw new \Exception("Unable to retrieve the stale Blue Badge data. {$ex}");
    }
    
    
    /*
     * Step 2: Delete the stale applicant and associated data.
     */
    try {
        // Delete the stale applicant and associated data
        $parkingApplication->deleteAllStaleApplicantData();
    
    } catch (\Exception $ex) {
        throw new \Exception("Unable to delete the stale applicant records and associated data. {$ex}");
    }
    
    
    /*
     * Step 3: Delete the remaining stale Blue Badge data
     */
    try {
        // Delete the stale Blue Badge data
        $parkingApplication->deleteAllStaleBlueBadgeData();
    
    } catch (\Exception $ex) {
        throw new \Exception("Unable to delete the stale Blue Badge data. {$ex}");
    }
    
    
    /*
     * Step 4: Delete the remaining stale permits, vehicles and associated data
     */
    try {
        // Delete the stale permits, vehicles and associated data
        $parkingApplication->deleteAllStalePermitData();
    
    } catch (\Exception $ex) {
        throw new \Exception("Unable to delete the stale permit records and associated data. {$ex}");
    }
    

} catch (\Exception $ex) {
    die($ex);
}

?>
