<?php
// Define class namespace
namespace UoSCore;

// Include class files
require_once('C:\hosting\websites\parking.sunderland.ac.uk\lib\ext\ldaprecord\vendor\autoload.php');
require_once('C:\hosting\websites\parking.sunderland.ac.uk\lib\classes\core\UosUtils.php');

/**
 * Utilises the LdapRecord class for usage of Active Directory.
 * 
 * 
 * @author Paul Cranner <paul.cranner@sunderland.ac.uk>
 * @copyright 2022 University of Sunderland
 * @license Proprietary
 * @version 1.0.0
 * @package UosCore
 */
class AdUtils {
	/**
     * The account suffix for the domain.
     * 
     * @var string
     */
	protected $accountSuffix = "@uni.ad.sunderland.ac.uk";
	
	/**
     * The base DN for the domain.
     * 
     * If this is set to null then adLDAP will attempt to obtain this automatically from the `rootDSE`.
     * 
     * @var string
     */
	protected $baseDn = "DC=uni,DC=ad,DC=sunderland,DC=ac,DC=uk";
	
	 /**
     * Administrator/Service account username.
     * 
     * @var string
     */
	protected $adminUsername = "svc_PHP";
	
	/**
     * Administrator/Service account password.
     *
     * @var string
     */
	protected $adminPassword = ":XwHAy-3RPf&cxuGTUxE^x";
	
	/**
     * Port used to connect to the domain controllers.
     *  
     * @var int
     */
	protected $adPort = 389;
		
	/**
     * Array of domain controllers for load balancing.
     * 
     * @var array
     */
	protected $domainControllers = array(
		/*'uos-uni-dc-06.uni.ad.sunderland.ac.uk',
		'uos-uni-dc-07.uni.ad.sunderland.ac.uk',
		'uos-uni-dc-08.uni.ad.sunderland.ac.uk',
		'uos-uni-dc-09.uni.ad.sunderland.ac.uk');*/
		'uks-uni-dc-01.uni.ad.sunderland.ac.uk',
		'uks-uni-dc-02.uni.ad.sunderland.ac.uk',
		'ukw-uni-dc-01.uni.ad.sunderland.ac.uk');
	
	/**
     * Connection object.
     * 
     * @var object
     */
	protected $connection;
   	
	
	/**
     * Constructor.
     * 
     * Initialises AD connetion object.
     * 
     * @since 1.0.0
     */
	function __construct() {
		$this->connection = new \LdapRecord\Connection([
			'hosts' => $this->domainControllers,
			'port' => $this->adPort,
			'base_dn' => $this->baseDn,
			'username' => $this->adminUsername,
			'password' => $this->adminPassword
		]);
	}
	
	/**
     * Get the properties for a specific account.
     * 
     * @param string $userAccount The username of the account the properties need to be returned for.
     * @return array Returns an array of account properties.
	 * @throws Exception Throws exception if unable to connect to AD server or user account not found.
     * @since 1.0.0
     */
	public function getAccountProperties($userAccount) {
        $uosUtils = new \CarParkingSystem\UosUtils();
		
		try {
			// Connect to server
			$this->connection->connect();
			
			// Create query object and add attributes to return
			$query = $this->connection->query();
			$query->addSelect('sAMAccountName', 
				'givenName', 
				'sn', 
				'displayName', 
				'mail', 
				'departmentNumber', 
				'employeeNumber', 
				'extensionAttribute1', 
				'extensionAttribute2', 
				'extensionAttribute3', 
				'extensionAttribute4', 
				'pwdLastSet', 
				'lastLogonTimestamp', 
				'accountExpires', 
				'userAccountControl');
			
			// Get result
			$record = $query->findByOrFail('samaccountname', $userAccount);

			// Save user properties
			$acctProps = array(
				'username' => (isset($record['samaccountname'][0])) ? $record['samaccountname'][0] : NULL,
				'firstName' => (isset($record['givenname'][0])) ? $record['givenname'][0] : NULL,
				'surname' => (isset($record['sn'][0])) ? $record['sn'][0] : NULL,
				'displayName' => (isset($record['displayname'][0])) ? $record['displayname'][0] : NULL,
				'emailAddr' => (isset($record['mail'][0])) ? $record['mail'][0] : NULL,
				'department' => (isset($record['departmentnumber'][0])) ? $record['departmentnumber'][0] : NULL,
				'employeeNumber' => (isset($record['employeenumber'][0])) ? $record['employeenumber'][0] : NULL,
				'userType' => (isset($record['extensionattribute1'][0])) ? $record['extensionattribute1'][0] : NULL,
				'libraryBarcode' => (isset($record['extensionattribute2'][0])) ? $record['extensionattribute2'][0] : NULL,
				'studentNumber' => (isset($record['extensionattribute3'][0])) ? $record['extensionattribute3'][0] : NULL,
				'programmeCode' => (isset($record['extensionattribute4'][0])) ? $record['extensionattribute4'][0] : NULL,
				'pwdLastSet' => (isset($record['pwdlastset'][0])) ? $uosUtils->convertMSWindowsTimeToUnixTime($record['pwdlastset'][0]) : NULL,
				'lastLogon' => (isset($record['lastlogontimestamp'][0])) ? $uosUtils->convertMSWindowsTimeToUnixTime($record['lastlogontimestamp'][0]) : NULL,
				'accountExpires' => (isset($record['accountexpires'][0])) ? $uosUtils->convertMSWindowsTimeToUnixTime($record['accountexpires'][0]) : NULL,
				'accountStatusFlagValue' => (isset($record['useraccountcontrol'][0])) ? $record['useraccountcontrol'][0] : NULL,
				'accountStatus' => (isset($record['useraccountcontrol'][0])) ? $this->getAccountUACValueDescription($record['useraccountcontrol'][0]) : NULL);			
			
			// Close connection
			$this->connection->disconnect();
			
			// Return user properties
			return $acctProps;
		
		} catch (\LdapRecord\Auth\BindException $ex) {
			$error = $ex->getDetailedError();
			print("<script>console.log('Unable to connect to server: [".$error->getErrorCode()."] ".$error->getErrorMessage()."');</script>");
			throw new \Exception('Unable to connect to server.');
		
		} catch (\LdapRecord\Query\ObjectNotFoundException $ex) {
			print("<script>console.log('User does not exist.');</script>");
			throw new \Exception('User '.$userAccount.' does not exist.');
		
		} catch (\Exception $ex) {
			print("<script>console.log('There was a problem processing the request. ".$ex->getMessage()."');</script>");
			throw new \Exception('There was a problem processing the request.');
		}
	}
	
	
	/**
     * Get the AD groups specified account is a member of.
     * 
     * @param string $userAccount The username of the account the groups need to be returned for.
     * @return array Returns an array of groups.
	 * @throws Exception Throws exception if unable to connect to AD server or user account not found.
     * @since 1.0.0
     */
	public function getAccountGroups($userAccount) {
		try {
			// Connect to server
			$this->connection->connect();
			
			// Create query object and add attributes to return
			$query = $this->connection->query();
			$query->addSelect('memberof');
			
			// Get result
			$record = $query->findByOrFail('samaccountname', $userAccount);
			
			$acctGroups = array();
			if(isset($record['memberof'])) {
				foreach($record['memberof'] as $group) {
					// Extract group name - i.e. just the name after the first CN=
					// CN=uit-web,OU=Application Groups,OU=Groups,DC=uni,DC=ad,DC=sunderland,DC=ac,DC=uk
					$groupCN = explode("¬", str_replace(['=',','], "¬", $group));
					if(count($groupCN) > 1 && $groupCN[0] == 'CN') array_push($acctGroups, $groupCN[1]);
				}
			}
			
			// Close connection
			$this->connection->disconnect();
			
			// Return user groups
			return $acctGroups;
			
		} catch (\LdapRecord\Auth\BindException $ex) {
			$error = $ex->getDetailedError();
			print("<script>console.log('Unable to connect to server: [".$error->getErrorCode()."] ".$error->getErrorMessage()."');</script>");
			throw new \Exception('Unable to connect to server.');
		
		} catch (\LdapRecord\Query\ObjectNotFoundException $ex) {
			print("<script>console.log('User does not exist.');</script>");
			throw new \Exception('User '.$userAccount.' does not exist.');
		
		} catch (\Exception $ex) {
			print("<script>console.log('There was a problem processing the request. ".$ex->getMessage()."');</script>");
			throw new \Exception('There was a problem processing the request.');
		}
		
		
	}
	
	/**
     * Check if user is a member of specified group(s).
     * 
     * @param string $userAccount The username of the account the groups need to be returned for.
	 * @param array/string $userGroups Group(s)s to check.
     * @return boolean Returns true if user is a member of any of the specified groups.
     * @since 1.0.0
     */
	public function isAccountInGroup($userAccount, $userGroups) {
		// Get a list of all groups user is a member of
		$groups = $this->getAccountGroups($userAccount);

		if (is_array($userGroups)) {
			// Check for a match
			foreach ($userGroups as $userGroup) {
				if (in_array($userGroup, $groups, true)) {
					return true;
				}
			}
		} else {
			// Check for match
			if (in_array($userGroups, $groups, true)) {
				return true;
			}
		}
		return false;
	}
	
	/**
     * Check if specified student number exists in AD.
     * 
     * @param string $studentNumber The student number to check.
     * @return boolean Returns true if the student number exists.
     * @since 1.0.0
     */
	public function isValidStudentNumber($studentNumber) {
		try {
			$returnVar = $this->attributeHasValue('extensionAttribute3', $studentNumber);
			
		} catch (\Exception $ex) {
			print("<script>console.log('There was a problem processing the request. ".$ex->getMessage()."');</script>");
			throw new \Exception('There was a problem processing the request.');
		}
		return $returnVar;
	}

	/**
     * Check if a specified attribute has a specified value.
     * 
     * @param string $attribute The AD attribute to check.
	 * @param string $value The value to check.
     * @return boolean Returns true if the specified attribute has the specified value.
     * @since 1.0.0
     */
	private function attributeHasValue($attribute, $value) {
		try {
			// Connect to server
			$this->connection->connect();
			
			// Create query object and add attributes to return
			$query = $this->connection->query();
			$query->addSelect('sAMAccountName');
			
			// Get result
			$record = $query->findByOrFail($attribute, $value);

			// Close connection
			$this->connection->disconnect();
			
			// Record found
			return true;
		
		} catch (\LdapRecord\Query\ObjectNotFoundException $ex) {
			// Close connection
			$this->connection->disconnect();
			
			// Record not found
			return false;

		} catch (\LdapRecord\Auth\BindException $ex) {
			$error = $ex->getDetailedError();
			print("<script>console.log('Unable to connect to server: [".$error->getErrorCode()."] ".$error->getErrorMessage()."');</script>");
			throw new \Exception('Unable to connect to server.');
		
		} catch (\Exception $ex) {
			print("<script>console.log('There was a problem processing the request. ".$ex->getMessage()."');</script>");
			throw new \Exception('There was a problem processing the request.');
		}
	}
    
	/**
     * Get the description of the account's 'userAccountControl' attribute.
     * 
     * The account's `userAccountControl` value is used to map the decimal value to a string description.
     * 
     * @param int $userAccountControl The `userAccountControl` value.
     * @return string Description of the value.
     * @since 1.0.0
     * @link http://support.microsoft.com/kb/305144 How to use the 'UserAccountControl' flags to manipulate user account properties.
     * @link http://msdn.microsoft.com/en-us/library/ms677840.aspx 'ms-DS-User-Account-Control-Computed' attribute.
     * @link http://jackstromberg.com/2013/01/useraccountcontrol-attributeflag-values/ 'UserAccountControl' attribute/flag values.
     */
	private function getAccountUACValueDescription($userAccountControl) {
		$uacDescription = "Unknown";

		if ($userAccountControl === "512") { $uacDescription = "Enabled"; }
		if ($userAccountControl === "514") { $uacDescription = "Disabled"; }
		if ($userAccountControl === "528") { $uacDescription = "Enabled; locked out"; }
		if ($userAccountControl === "530") { $uacDescription = "Disabled; locked out"; }
		if ($userAccountControl === "544") { $uacDescription = "Enabled; password not required"; }
		if ($userAccountControl === "546") { $uacDescription = "Disabled; password not required"; }
		if ($userAccountControl === "66048") { $uacDescription = "Enabled; password never expires"; }
		if ($userAccountControl === "66050") { $uacDescription = "Disabled; password never expires"; }
		if ($userAccountControl === "66080") { $uacDescription = "Enabled; password never expires and not required"; }
		if ($userAccountControl === "66082") { $uacDescription = "Disabled; password never expires and not required"; }
		if ($userAccountControl === "8389120") { $uacDescription = "Enabled; password expired"; }
		if ($userAccountControl === "8389122") { $uacDescription = "Disabled; password expired"; }

		return $uacDescription;
	}
    
    public function authenticate($userName, $password) {
        // Attempt to bind to LDAP
        if ($this->connection->auth()->attempt($userName.'@sunderland.ac.uk', $password)) {
            // Authenticated
            return true;
        } else {
            // Incorrect user name/password
            return false;
        }
    }
}
?>