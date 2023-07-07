<?php

// User Model Object
// empty User Exception class so we can catch errors
class UserException extends Exception { }

class User {
	// define private variables
	// define variable to store user id number
	private $_id;
  // define variable to store user name
  private $_name;
	// define variable to store branch
	private $_branchid;
  // define variable to store username
  private $_username;
  // define variable to store user status
  private $_status;
  // define variable to store user role
  private $_role;
	// define variable to store user password
  private $_password;
	// define variable to store user contact
	private $_contact;
	// define variable to store  sacco
	private $_sacco;
	// define variable to store branch
	private $_branch;
  // constructor to create the user object with the instance variables already set
	public function __construct($id, $name, $username, $password, $contact, $status, $role, $branchid, $sacco, $branch) {
		$this->setID($id);
		$this->setName($name);
		$this->setBranchID($branchid);
    $this->setUsername($username);
    $this->setRole($role);
    $this->setPassword($password);
		$this->setStatus($status);
		$this->setContact($contact);
		$this->setSacco($sacco);
		$this->setBranch($branch);

	}
  // function to return user ID
	public function getID() {
		return $this->_id;
	}

  // function to return user full name
	public function getName() {
		return $this->_name;
	}

  // function to return branch ID
	public function getBranchID() {
		return $this->_branchid;
	}

  // function to return username
	public function getUsername() {
		return $this->_username;
	}

  // function to return username
  public function getRole() {
    return $this->_role;
  }

  // function to return user status
	public function getStatus() {
		return $this->_status;
	}

  // function to return user password
	public function getPassword() {
		return $this->_password;
	}
	// function to return user contact
	public function getContact() {
		return $this->_contact;
	}
	// function to return user contact
	public function getSacco() {
		return $this->_sacco;
	}
	// function to return Branch
	public function getBranch() {
		return $this->_branch;
	}

	// function to set the private user ID
	public function setID($id) {
		// if passed in User ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new UserException("User ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private user full name
	public function setName($name) {
		// if passed in userfull name is not between 1 and 255 characters
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new UserException("Invalid user full name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private branch
  public function setBranchID($branchid) {
    // if passed in Branch ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
    if(!is_numeric($branchid) || $branchid <= 0 || $branchid > 9223372036854775807):
      throw new UserException("User branchid error");
    endif;
    $this->_branchid = $branchid;
  }

  // function to set the private Username
  public function setUsername($username) {
    // if passed in username is not between 1 and 255 characters
    if(strlen($username) < 0 || strlen($username) > 255):
      throw new UserException("User username error");
    endif;
    $this->_username = $username;
  }

	// function to set the private Contact
	public function setContact($contact) {
		// if passed in contact is not null between 1 and 11 characters
		if(!is_numeric($contact) || strlen($contact) <= 0 || strlen($contact) > 11):
			throw new UserException("user contact error");
		endif;
		$this->_contact = $contact;
	}

  // function to set the private role
  public function setRole($role) {
    // if passed in role is not a manager, loansofficer, teller, agent
    if(strtolower($role) !== 'manager' && strtolower($role) !== 'agent' && strtolower($role) !== 'teller' && strtolower($role) !== 'loansofficer'):
      throw new UserException("User role error");
    endif;
    $this->_role = $role;
  }

  // function to set the private password
  public function setPassword($password) {
    // if passed in password is between 1 >< 255
    if(strlen($password) < 0 || strlen($password) > 255):
      throw new UserException("User Password error");
    endif;
    $this->_password = $password;
  }

	// function to set the private password
	public function setSacco($sacco) {
		// if passed in password is between 1 >< 255
		if(strlen($sacco) < 0 || strlen($sacco) > 255):
			throw new UserException("sacco Password error");
		endif;
		$this->_sacco = $sacco;
	}

  // function to set the private status type
  public function setStatus($status) {
    // if passed in user is active or inactive
    if(strtolower($status) !== 'active' && strtolower($status) !== 'inactive'):
      throw new UserException("User status error");
    endif;
    $this->_status = $status;
  }
	// function to set the private password
	public function setBranch($branch) {
		// if passed in password is between 1 >< 255
		if(strlen($branch) < 0 || strlen($branch) > 255):
			throw new UserException("sacco Branch error");
		endif;
		$this->_branch = $branch;
	}


  // function to return user object as an array for json
	public function returnUserAsArray() {
		$user = array();
		$user['id'] = $this->getID();
		$user['name'] = $this->getName();
    $user['username'] = $this->getUsername();
    $user['password'] = $this->getPassword();
		$user['contact'] = $this->getContact();
    $user['status'] = $this->getStatus();
    $user['role'] = $this->getRole();
		$user['branchid'] = $this->getBranchID();
		$user['sacco'] = $this->getSacco();
		$user['branch'] = $this->getBranch();
		return $user;
	}
	//reset all the prefered according the php version
	// void static function axey(){
	// 				$target => '7.2';
	// }

}
