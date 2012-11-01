<?php use \Config;
/**
 * LDAP Class provides basic operations for authenticating users and getting details from them.
 *
 * @author Carl Saggs (based on Rikki Carroll's original LDAP Class)
 * @version 2011.10.05
 *
 * Example Usage:
 *		$ldap = new LDAP("ldaps://ldap.id.kent.ac.uk",636);
 * 		$ldap->setBaseUser('uid=pwd_reset_dev,ou=Special Accounts,o=uni','');
 * 		$ldap->setBaseRDN('o=kent.ac.uk,o=uni');
 *
 *		$ldap->authenticateUser("<username>", "<password>");
 *		$ldap->getDetails("<username>");
  */

class KentLDAP {
	//stores the address of the LDAP server
	private $ldap_server_address;
	private $ldap_connection;

	//Auth
	private $ldap_base_user ='';
	private $ldap_base_pass ='';
	private $ldap_base_rdn ='o=kent.ac.uk,o=uni';

	private static $instance = null;
	public static function instance(){
		if(self::$instance == null){
			self::$instance = new KentLDAP(Config::get('ldap.host'), Config::get('ldap.port'));
			self::$instance->setBaseRDN(Config::get('ldap.base_dn'));
			return self::$instance;
		}else{
			return self::$instance;
		}

	}

	/**
	 * Creates a instance of the ldap class.
	 * Requires one parameter - the address of the LDAP server.
	 *
	 * Example Useage:
	 *		$ldapObj = new LDAP("ad.kent.ac.uk");
	 *
	 * @param $address string The address of the LDAP server.
	 */
	public function __construct($address,$port=389) {
		$this->ldap_server_address = $address;
		$this->ldap_connection = ldap_connect($address, $port);
	}

	/**
	 * Set the base user for LDAP
	 * @param $user (full dsn)
	 * @param $password 
	 */
	public function setBaseUser($user,$pass){
		$this->ldap_base_user = $user;
		$this->ldap_base_pass = $pass;
	}
	
	/**
	 * Set the base user for RDN
	 * @param $rdn Location to search for required feilds in
	 */
	public function setBaseRDN($rdn){
		$this->ldap_base_rdn = $rdn;
	}

	/**
	 * Returns whether the current LDAP Request object is connected to a server.
	 * @return True if connected, false otherwise.
	 */
	public function isConnected() {
		if ($this->ldap_connection) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Disconnects any current connection to an ldap server.
	 */
	public function disconnect() {
		@ldap_close($this->ldap_connection);
	}

	/**
	 * Returns the address of the current LDAP server address.
	 * @returns The string representing the address of the LDAP server.
	 */
	public function getLDAPServerAddress()
	{
		return $this->ldap_server_address;
	}
	
	/**
	 * Run automatically by PHP when the object is removed from memory.
	 * Here we basically clean up and close the connection to the ldap server.
	 */
	public function __destruct() {
		$this->disconnect();
		unset($this->ldap_server_address);
		unset($this->ldap_connection);
	}

	/**
	 * Return infomation from a user based on there UID
	 *	@param $uid
	 *  @return array
	 */
	public function getDetails($username) {
		return $this->ldap_query($username);
	}

	/**
	 * Return infomation from a user based on there student number
	 *	@param $student_number
	 *  @return array
	 */
	public function getDetailsByStudentNumber($sno) {
		return $this->ldap_query($sno,'unikentsnumber');
	}

	/**
	 * Return infomation from a user based on there Surname
	 *	@param $sn
	 *  @return array
	 */
	public function getDetailsBySurname($sn){
		return $this->ldap_query($sn,'sn');
	}

	/**
	 * If found returns an array containing the records of matched person details
	 * If not found returns false
	 *
	 * "*" can be used as a wildcard.
	 *
	 * Example:
	 *		$lr = new LDAP("ad.kent.ac.uk");
	 *		$matchedUsers = $lr->getDetails("<username>");
	 *		$matchedUsers[recordNumber][property][0]
	 *
	 *		Valid Properties: uid, cn, givenName, sn, mail
	 *
	 * @return An array of users in the format described in the description of this method.
	 */
	private function ldap_query($value, $on='uid'){

		//Ensure we have a base user
		$this->checkBaseUser();
		//Bind request
		$this->createRequest($this->ldap_connection, $this->ldap_base_user, $this->ldap_base_pass);
		//Search Data
		$sr = ldap_search($this->ldap_connection, $this->ldap_base_rdn, "{$on}={$value}");
		//Return data as array
		$info = ldap_get_entries($this->ldap_connection, $sr);
		//Check data has returned successfully
		if($info == null || $info['count'] == 0) $info = array('error' => 'An error Occured: '.ldap_error($this->ldap_connection));
		return $info;
	
	}

	public function getError(){
		if($this->ldap_connection == null) return "no ldap connection";

		return ldap_error($this->ldap_connection);
	}
	/**
	 * Update a given users Password
	 *	@param $user UID of user
	 *	@param $newPassword new password for user
	 *	@return bool Success status True/false
	 */
	public function updatePassword($user,$newPassword){
		return $this->updateUser($user, array('userpassword'=>$newPassword));
	}

	/**
	 * Update an attribute(or attribites) for a user
	 *	@param $user UID of user
	 *	@param Array of new attributes to set on user
	 *	@return bool Success status True/false
	 */
	public function updateUser($uid, $details){
		//$details array('userpassword'=>$newPassword)
		$modified = false;

		//Find user
		$student_dn = $this->getUserDn($uid);

		//Do the update
		if (ldap_modify($this->ldap_connection, $student_dn, $details)) {
			$modified = true;
		}

		return $modified;
	}

	/**
	 * Remove an LDAP attribute from a user
	 *
	 * @param UID (user id such as at369 )
	 * @param name of attribute to remove
	 * @return true|false success
	 */
	public function removeUserAttribute($uid,$attribute){
		$modified = false;
		//Find user
		$student_dn = $this->getUserDn($uid);
		//Attribute to remove array
		$arr[$attribute] = array();
		//remove the attribute
		if (ldap_mod_del($this->ldap_connection, $student_dn, $arr)) {
			$modified = true;
		}
		return $modified;
	}

	/**
	 * Get a user's DN
	 * @param UID (user id such as at369 )
	 * @return Fully qualifed DN of user (or null if they do not exist/cannot be found)
	 */
	private function getUserDn($uid){
		//die("getdn");
		//Bind Request
		$this->createRequest($this->ldap_connection, $this->ldap_base_user, $this->ldap_base_pass);
		
		//look em up
		$sr = ldap_search($this->ldap_connection, $this->ldap_base_rdn, "uid={$uid}");
		//get specific user
		$first = ldap_first_entry($this->ldap_connection, $sr);
		if($first===false)return null;
		return  ldap_get_dn($this->ldap_connection, $first);

	}

	/**
	 * Ensure base user is set before carrying out dependant operations
	 * @throws Exception When base user is not set
	 *
	 */
	private function checkBaseUser(){
		if($this->ldap_base_user =='' || $this->ldap_base_pass =='' || $this->ldap_base_user == null){
			throw new Exception("Base user has not been defined. A base user is required in order to perform read operations on users.");
		}
	}

	/**
	 * Get a user by username
	 * @param $username
	 * @return user as a LDAPUser Object
	 *
	 */
	function getUserObject($username) {

		$details = $this->getDetails($username);
		if(!isset($details['error'])){
			return new LDAPUser($details[0], $this);
		}else{
			return null;
		}
	}

	/**
	 * Validate username and Password Against LDAP
	 * @param string $username
	 * If authentication was succesful returns true
	 * If authentication was not succesful returns false
	 */
	public function authenticateUser($username, $password) {
		$ds = $this->ldap_connection;
		if ($ds) {
			//when binding password must not be empty, otherwise the it will validate based on username only
			if(empty($password)) $password = " ";
			//get user string (+ return false if user doesn't exist)
			$user_dn = $this->getUserDn($username);
			if($user_dn == null) return false;
			//die ($user_dn);
			//Attempt to Bind 
			$r = $this->createRequest($ds, $user_dn ,$password);
			if($r) {
				return true;
			}
		}
		return false;
	}
	public function getAuthenticateUser($username, $password) {
		$ds = $this->ldap_connection;
		if ($ds) {
			//when binding password must not be empty, otherwise the it will validate based on username only
			if(empty($password)) $password = " ";
			//get user string (+ return false if user doesn't exist)
			$user_dn = $this->getUserDn($username);
			if($user_dn == null) return false;
			//die ($user_dn);
			//Attempt to Bind 
			$r = $this->createRequest($ds, $user_dn ,$password);
			if($r) {
				$sr = ldap_search($this->ldap_connection, $this->ldap_base_rdn, "uid={$username}");
				return ldap_get_entries($this->ldap_connection, $sr);
			}
		}
		return false;
	}




	//Search Data
		

	/**
	 * Connect to LDAP with given credentals. (Bind)
	 *
	 * @param $ds LDAP Connection
	 * @param $usr User(full dn) to bind to ldap with
	 * @param $pass password to autenticate ldap with
	 */
	private function createRequest($ds, $usr, $pass) {


		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if(@ldap_bind($ds, $usr, $pass)){
			return true;
		}else{
			return false;//Return false rather than throwing error
		}
	}
}




/**
 * Class returned from LDAP::getUserObject().
 * Provides quick methods to get basic user details.
 */
class LDAPUser {

	private $info;
	private $ldap;

	/**
	 * Contructor
	 * @param array $info A users info.
	 */
	public function __construct($info, $ldap) {
		$this->info = $info;
		$this->ldap = $ldap;
	}

	/**
	 * Get a user's full/display name.
	 * @return string Users full/display name
	 */
	public function getDisplayName() {
		return $this->info['displayname'][0];
	}


	/**
	 * Get an Attribute from a user.
	 *
	 * @param string Attribute name.
	 * @return string Value of Attribute
	 */
	public function getAttribute($attr) {
		if(isset($this->info[$attr][0])){
			return $this->info[$attr][0];
		}else{
			return null;
		}
	}

	/**
	 * Set an attribute for this user.
	 *
	 * @param string Attribute name.
	 * @param string Value of Attribute
	 * @return success true|false
	 */
	public function setAttribute($attr, $value) {
		$uid = $this->info['uid'][0];
		$data = array($attr => $value);
		return $this->ldap->updateUser($uid, $data);
	}

	/**
	 * delete an attribute for this user.
	 *
	 * @param string Attribute name.
	 * @return success true|false
	 */
	public function deleteAttribute($attr) {
		$uid = $this->info['uid'][0];
		return $this->ldap->removeUserAttribute($uid, $attr);
	}

	/**
	 * Get user's username.
	 * @return string User's username
	 */
	public function getUserName() {
		return $this->info['uid'][0];
	}

	/**
	 * Get user's Email.
	 * @return string User's username
	 */
	public function getEmail() {
		return $this->info['mail'][0];
	}

	/**
	 * Is this user a Student
	 * @return Boolean
	 */
	public function isStudent(){
		return ($this->getAccountType()=='Student');
	}

	/**
	 * Is this user an Alumni
	 * @return Boolean
	 */
	public function isAlumni(){
		return ($this->getAccountType()=='Alumni');
	}

	/**
	 * Is this user a Staff Member
	 * @return Boolean
	 */
	public function isStaff(){
		return ($this->getAccountType()=='Staff');
	}

	/**
	 * Get user Type.
	 * @return string User's Type based on there DN
	 */
	public function getAccountType() {
		if(strpos($this->info['dn'],'ou=students') != false){
			return 'Student';
		}else if(strpos($this->info['dn'],'ou=staff') != false){
			return 'Staff';
		}else if(strpos($this->info['dn'],'ou=alumni') != false){
			return 'Alumni';
		}else{
			return 'Unknown';
		}
	}

}