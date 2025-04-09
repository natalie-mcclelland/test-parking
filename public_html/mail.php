<?php
//generateEmailApplicationSubmitted($applicantTitle, $applicantFirstName, $applicantSurname, $applicantEmailAddress, $permitType, $permitValidFrom, $permitValidTo, $vehicle1VRM, $vehicle2VRM = NULL) {
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once(dirname(__FILE__)."/../lib/classes/application/ParkingApplication.php");
$parkingApplication = new \CarParkingSystem\ParkingApplication();

$parkingApplication->generateEmailApplicationSubmitted("Test", "Paul", "Cranner", "paul.cranner@sunderland.ac.uk", "Test", "2024-05-01", "2024-08-31", "Test");
?>