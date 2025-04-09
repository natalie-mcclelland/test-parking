<?php

// Define class namespace
namespace CarParkingSystem;

// Include library files
//require_once(dirname(__FILE__) . "/../../ext/DNSBarcode/barcodes.php");


/**
 * Utility functions.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.0.0
 * @package CarParkingSystem
 */
class UosUtils {

    /**
     * The character length the generated token.
     * 
     * @var string The number of characters to generate.
     */
    private $tokenLength = 8;
    
    /**
     * The character length the generated password.
     * 
     * @var string The number of characters to generate.
     */
    private $passwordLength = 14;

    /**
     * Constructor.
     * 
     * @since 1.0.0
     */
    function __construct() {
        
    }

    /**
     * Generate a cryptographically secure random number.
     * 
     * A result which is truncated using the modulo operator ( `%` ) is not cryptographically secure, as the
     * generated numbers are not equally distributed and thus some numbers may occur more often than others.
     * Therefore if the result is too large then just drop it and gerneate a new one.
     * 
     * @param int $min The minimum number to calcuate from.
     * @param int $max The maximum number to calcuate to.
     * @return int The random number.
     * @since 1.0.0
     */
    public function cryptographicRandomNumber($min, $max) {
        // The range in which to calculate from
        $range = $max - $min;
        if ($range < 0)
            // Not quite so random...
            return $min;

        // Logarithm
        $log = log($range, 2);

        // Length in bytes
        $bytes = (int) ($log / 8) + 1;

        // Length in bits
        $bits = (int) $log + 1;

        // Set all lower bits to 1
        $filter = (int) (1 << $bits) - 1;

        // Calculate a random number
        do {
            // Genrate psuedo-random bytes and convert them
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));

            // Discard irrelevant bits
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);

        // Return the random number
        return $min + $rnd;
    }

    /**
     * Generate a random password.
     * 
     * Passwords must be complex and contain:-
     *  + At least 8 characters
     *  + Consist of letters and numbers
     *  + At least 1 character must be a capital
     * 
     * They do not necessarily need to contain punctuation characters.
     * 
     * The generated password is created by using a cryptographically secure random number and selecting a
     * character from a pre-defined character set, a process which is repeated until the required length
     * is reached.  The character set consists of:-
     * 
     *  + Lowercase alphabet
     *  + Uppercase alphabet
     *  + Numeric
     *  + Punctuation
     *     - Characters: `!` `£` `$` `%` `^` `&` `*` `(` `)` `-` `_` `=` `+` `@` `#` `,` `<` `.` `>` `?`
     * 
     * @return string The generated password.
     * @since 1.0.0
     * @see generateRandomToken()
     */
    protected function generateAccountPassword() {
        // NULLify in case previous password is still stored
        $generatedPassword = NULL;

        // The characters to use in the password
        $charSet = array_merge(
                range('a', 'z'), range('A', 'Z'), range(0, 9), array('!', '£', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '@', '#', ',', '<', '.', '>', '?')
        );

        // Generate the password
        for ($i = 0; $i < $this->passwordLength; $i++) {
            $generatedPassword .= $charSet[$this->cryptographicRandomNumber(0, count($charSet))];
        }

        // Return the generated password
        return $generatedPassword;
    }

    /**
     * Generate a random token.
     * 
     * @return string The generated token.
     * @since 1.0.0
     * @see generateAccountPassword()
     */
    public function generateRandomToken() {
        // NULLify in case previous token is still stored
        $generatedToken = NULL;

        // The characters to use in the token
        $charSet = array_merge(
                range('a', 'z'), range('A', 'Z'), range(0, 9)
        );

        // Generate the token
        for ($i = 0; $i < $this->tokenLength; $i++) {
            $generatedToken .= $charSet[$this->cryptographicRandomNumber(0, count($charSet))];
        }

        // Return the generated token
        return $generatedToken;
    }
    
    /**
     * Generate an unique ID.
     * 
     * @param string $userAccount The username of the user account.
     * @param string $timestamp The timestamp that should be included.
     * @return string The unique ID.
     * @since 1.0.0
     */
    public function generateUniqueID($userAccount, $timestamp) {
        $token = $this->generateToken();
        $uniqueID = "{$userAccount}-{$timestamp}-{$token}";
        return $uniqueID;
    }

    /**
     * Generate a PDF417 barcode containing the unique ID.
     * 
     * @param string $transactionID The unique ID.
     * @return string The generated barcode.
     * @since 1.0.0
     *\\/
    public function generatePDF417Barcode($uniqueID) {
        $bcLib = new \DNS2DBarcode();
        $barcode = $bcLib->getBarcodePNG($uniqueID, 'PDF417', 2, 2, array(0, 0, 0));
        return $barcode;
    }*/

    /**
     * Convert a Microsoft Windows timestamp to a Unix timestamp.
     * 
     * The timestamp is converted from a Microsoft Windows timestamp, which is based upon the Microsoft Windows epoch
     * of _January 1, 1601 (UTC)_, to a Unix timetamp, which is based upon the Unix epoch of _January 1, 1970 (UTC)_.
     * This between the two epochs is *11644473600* seconds.
     * 
     * Microsoft Windows stores timestamp values as 100 nanosecond intervals since it's epoch.
     * 
     * Caution: Converting between the timestamps and converting back may result in a slightly different value due to
     * rounding, but should not affect the human readable timestamp.
     * 
     * @param int $msWindowsTime
     * @return int $unixTime
     * @since 1.0.0
     * @see convertUnixTimeToMSWindowsTime()
     * @link http://msdn.microsoft.com/en-us/library/ms974598.aspx MSDN documentation of timestamped attributes.
     * @link http://en.wikipedia.org/wiki/Epoch_%28reference_date%29#Notable_epoch_dates_in_computing Wikipedia article on different epochs.
     */
    public function convertMSWindowsTimeToUnixTime($msWindowsTime) {
        $unixTime = intval(round($msWindowsTime / 10000000) - 11644473600);
        return $unixTime;
    }

    /**
     * Convert a Unix timestamp to a Microsoft Windows timestamp.
     * 
     * The timestamp is converted from a Unix timetamp, which is based upon the Unix epoch of _January 1, 1970 (UTC)_, to
     * a Microsoft Windows timestamp, which is based upon the epoch of _January 1, 1601 (UTC)_.  This between the two epochs
     * is *11644473600* seconds.
     * 
     * Microsoft Windows stores timestamp values as 100 nanosecond intervals since it's epoch.
     * 
     * Caution: Converting between the timestamps and converting back may result in a slightly different value due to
     * rounding, but should not affect the human readable timestamp.
     * 
     * @param int $unixTime
     * @return int $msWindowsTime
     * @since 1.0.0
     * @see convertMSWindowsTimeToUnixTime()
     * @link http://msdn.microsoft.com/en-us/library/ms974598.aspx MSDN documentation of timestamped attributes.
     * @link http://en.wikipedia.org/wiki/Epoch_%28reference_date%29#Notable_epoch_dates_in_computing Wikipedia article on different epochs.
     */
    public function convertUnixTimeToMSWindowsTime($unixTime) {
        $msWindowsTime = intval(round(($unixTime + 11644473600) * 10000000));
        return $msWindowsTime;
    }

}

?>
