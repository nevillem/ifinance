<?php
// Task Model Object

// empty MemberException class so we can catch task errors
class GroupRegistrationException extends Exception { }

class GroupRegistration {
	// define private variables
	private $_id;
	// define variable to store  firstname
	private $_groupname;
	// define variable to chairperson full name
  private $_chairperson;
	// define variable to store contact
	private $_contact;
	private $_email;
	// define variable to volunter account balance
	private $_account;
	// define variable to store address
	private $_address;
	// define variable to store dob
	private $_doj;
	//define employment statua
	private $_branch;
	//define gross income
	private $_status;
	// set type to group
	private $_type;
	private $_identification;
	//marital status
  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$account, $groupname,$chairperson, $contact,$email,
	$address, $doj, $branch,$status,
	$type,$identification) {
		$this->setID($id);
		$this->setAccount($account);
		$this->setGroupName($groupname);
		$this->setChairperson($chairperson);
		$this->setContact($contact);
		$this->setEmail($email);
		$this->setAddress($address);
		$this->setDoj($doj);
		$this->setStatus($status);
		$this->setType($type);
		$this->setBranch($branch);
		$this->setIDN($identification);
	}

  // function to return member ID
	public function getID() {
		return $this->_id;
	}
	// function to return member account
	public function getAccount() {
		return $this->_account;
	}
  // function to return member firstname
	public function getGroupname() {
		return $this->_groupname;
	}
	// function to return member lmidlename
	public function getGroupchairperson() {
		return $this->_chairperson;
	}
	// function to return member contact
	public function getContact() {
		return $this->_contact;
	}
	// function to return member email
	public function getEmail() {
		return $this->_email;
	}

	// function to return member address
	public function getAddress() {
		return $this->_address;
	}
  // function to return memeber identification
	public function getIDN() {
		return $this->_identification;
	}
	// function to return member doj
	public function getDoj() {
		return $this->_doj;
	}
  // function to return member status
	public function getStatus() {
		return $this->_status;
	}
	// function to return member type
	public function getType() {
		return $this->_type;
	}

	// function to return member type
	public function getBranch() {
		return $this->_branch;
	}

	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new GroupRegistrationException("group ID error");
		}
		$this->_id = $id;
	}
	// function to set the private member email
	public function setAccount($account) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($account) > 10) {
			throw new GroupRegistrationException("group account error");
		}
		$this->_account = $account;
	}
  // function to set the private firstname
	public function setGroupName($groupname) {
		// if passed in firstname is not between 1 and 255 characters
		if(strlen($groupname) < 1 || strlen($groupname) > 255) {
			throw new GroupRegistrationException("group name error");
		}
		$this->_groupname = $groupname;
	}

	// function to set the private lastname
	public function setChairperson($chairperson) {
		// if passed in lastname is not between 1 and 255 characters
		if(strlen($chairperson) < 1 || strlen($chairperson) > 255) {
			throw new GroupRegistrationException("group chairperson error");
		}
		$this->_chairperson = $chairperson;
	}
	// function to set the private member contact
	public function setContact($contact) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($contact) < 1 || strlen($contact) > 255) {
			throw new GroupRegistrationException("group contact error");
		}
		$this->_contact = $contact;
	}
	// function to set the private member email
	public function setEmail($email) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($email) > 255) {
			throw new GroupRegistrationException("group email error");
		}
		$this->_email = $email;
	}
	// function to set the private member address
	public function setAddress($address) {
		// if passed in address is not between 1 and 255 characters
		if(strlen($address) > 16777215) {
			throw new GroupRegistrationException("group address error");
		}
		$this->_address = $address;
	}

	// function to set the private member doj
	public function setDoj($doj) {
		// if passed in doj is not between 1 and 255 characters
		if(strlen($doj) < 1 || strlen($doj) > 255) {
			throw new GroupRegistrationException("group doj error");
		}
		$this->_doj = $doj;
	}
	// function to set the private member idn
	public function setIDN($identification) {
		// if passed in identification is not between 1 and 255 characters
		if(strlen($identification) > 255) {
			throw new GroupRegistrationException("chairperson identification error");
		}
		$this->_identification = $identification;
	}
	public function setBranch($branch) {
		// if passed in identification is not between 1 and 255 characters
		// if(strlen($identification) > 255) {
		// 	throw new GroupRegistrationException("chairperson identification error");
		// }
		$this->_branch = $branch;
	}


	// function to set the private member status
	public function setStatus($status) {
		// if passed in status is not active or inactive
		if(!empty($status) ? (strtolower($status) !== 'active' && strtolower($status) !== 'inactive') : null) {
			throw new GroupRegistrationException("group status is not active or inactive");
		}
		$this->_status = strtolower($status);
	}

	// function to set the private member type
	public function setType($type) {
		// if passed in type
		if(strtolower($type) !== 'group') {
			throw new GroupRegistrationException("type must be group");
		}
		$this->_type = strtolower($type);
	}

  // function to return member object as an array for json
	public function returnGroupAsArray() {
		$group = array();
		$group['id'] = $this->getID();
		$group['account'] = $this->getAccount();
		$group['groupname'] = $this->getGroupname();
		$group['chairperson'] = $this->getGroupchairperson();
		$group['contact'] = $this->getContact();
		$group['email'] = $this->getEmail();
		// $group['gender'] = $this->getGender();
		$group['address'] = $this->getAddress();
		$group['doj'] = $this->getDoj();
		$group['identification'] = $this->getIDN();
		$group['type'] = $this->getType();
		$group['branch'] = $this->getBranch();
		$group['status'] = $this->getStatus();

		return $group;
	}

}
