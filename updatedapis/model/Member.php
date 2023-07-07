<?php
// Task Model Object

// empty MemberException class so we can catch task errors
class MemberException extends Exception { }

class Member {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store  firstname
	private $_firstname;
	// define variable to store lastname
		private $_midlename;
	// define variable to store contact
	private $_lastname;
	// define variable to store contact
	private $_contact;
	// define variable to store gender
	private $_gender;
	// define variable to store gender
	private $_email;
	// define variable to store address
	private $_address;
	// define variable to store dob
	private $_dob;
	// define variable to store doj
	private $_doj;
	// define variable to store identification
	private $_identification;
	// define variable to store status
	private $_status;
	// define variable to images array
	private $_images;
	// define variable to member type
	private $_type;
	// define variable to volunter account balance
	private $_account;
	//define employment statua
	private $_employment_status;
	//define gross income
	private $_gross_income;
	//marital status
	private $_marital_status;
	//marital status
	private $_sacco_groups;
	// define varible to keep other member
	private $_attach;
	// define varible to keep other member
	private $_balance;
	// define varible to keep other member
	private $_fbalance;
	// define varible to keep other member
	private $_cbalance;
	// define varible to keep other member
	// private $_sbalance;
	private $_nextOfkin;
	private $_accounts;
	// define varible to keep other member
// 	private $_category;

  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id, $firstname,$midlename, $lastname, $contact, $gender, $email, $address,
	$dob, $doj, $identification, $status, $type, $account,$employment_status,$gross_income,$marital_status,$sacco_groups= array(),
	$attach, $balance, $fbalance, $cbalance, $images = array(), $nextOfkin = array(), $accounts = array()) {
		$this->setID($id);
		$this->setFirstname($firstname);
		$this->setMidlename($midlename);
		$this->setLastname($lastname);
		$this->setContact($contact);
		$this->setGender($gender);
		$this->setEmail($email);
		$this->setAddress($address);
		$this->setDob($dob);
		$this->setDoj($doj);
		$this->setIDN($identification);
		$this->setStatus($status);
		$this->setType($type);
		$this->setEmploymentStatus($employment_status);
		$this->setGrossIncome($gross_income);
		$this->setMaritalStatus($marital_status);
		$this->setSaccoGroups($sacco_groups);
		$this->setAccount($account);
		$this->setAttach($attach);
		$this->setBalance($balance);
// 		$this->setCategory($category);
		$this->setCbalance($cbalance);
		$this->setFbalance($fbalance);
		// $this->setSbalance($sbalance);
		$this->setImages($images);
		$this->setNextOfKin($nextOfkin);
		$this->setAccounts($accounts);
	}

  // function to return member ID
	public function getID() {
		return $this->_id;
	}

  // function to return member firstname
	public function getFirstname() {
		return $this->_firstname;
	}
	// function to return member lmidlename
	public function getMidlename() {
		return $this->_midlename;
	}
	// function to return member lastname
	public function getLastname() {
		return $this->_lastname;
	}

	// function to return member contact
	public function getContact() {
		return $this->_contact;
	}

	// function to return member gender
	public function getGender() {
		return $this->_gender;
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

  // function to return member dob
	public function getDob() {
		return $this->_dob;
	}

	// function to return member doj
	public function getDoj() {
		return $this->_doj;
	}


  // function to return member status
	public function getStatus() {
		return $this->_status;
	}

	// function to return member images
	public function getImages() {
		return $this->_images;
	}
	// function to return member account
	public function getAccount() {
		return $this->_account;
	}
	// function to return member type
	public function getType() {
		return $this->_type;
	}

	// function to return member employement status
	public function getEmploymentStatus() {
		return $this->_employment_status;
	}

	// function to return member gross income
	public function getGrossIncome() {
		return $this->_gross_income;
	}
	// function to return member employement marital status
	public function getMaritalStatus() {
		return $this->_marital_status;
	}

	// function to return member employement sacco groups
	public function getSaccoGroups() {
		return $this->_sacco_groups;
	}
	// function to return member account
	public function getAttach() {
		return $this->_attach;
	}
	// function to return member account
	public function getBalance() {
		return $this->_balance;
	}
	// function to return member account
	// public function getSbalance() {
	// 	return $this->_sbalance;
	// }
	// function to return member account
	public function getCbalance() {
		return $this->_cbalance;
	}
	// function to return member account
	public function getFbalance() {
		return $this->_fbalance;
	}
	// function to return member next of kin
	public function getNextOfKIN() {
		return $this->_nextOfkin;
	}
	// function to return member next of kin
	public function getAccounts() {
		return $this->_accounts;
	}

	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new MemberException("Member ID error");
		}
		$this->_id = $id;
	}
	// function to set the private Member ID
// 	public function setCategory($category) {
// 		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
// 		// over nine quintillion rows
// 		if($category > 9223372036854775807) {
// 			throw new MemberException("Member category error");
// 		}
// 		$this->_category = $category;
// 	}


  // function to set the private firstname
	public function setFirstname($firstname) {
		// if passed in firstname is not between 1 and 255 characters
		if(strlen($firstname) < 1 || strlen($firstname) > 255) {
			throw new MemberException("Member firstname error");
		}
		$this->_firstname = $firstname;
	}

	// function to set the private midlename
	public function setMidlename($midlename) {
		// if passed in lastname is not between 1 and 255 characters
// 		if(strlen($midlename) < 1 || strlen($midlename) > 255) {
// 			throw new MemberException("member midlename error");
// 		}
		$this->_midlename = $midlename;
	}
	// function to set the private lastname
	public function setLastname($lastname) {
		// if passed in lastname is not between 1 and 255 characters
		if(strlen($lastname) < 1 || strlen($lastname) > 255) {
			throw new MemberException("member lastname error");
		}
		$this->_lastname = $lastname;
	}
	// function to set the private member contact
	public function setContact($contact) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($contact) < 1 || strlen($contact) > 255) {
			throw new MemberException("member contact error");
		}
		$this->_contact = $contact;
	}
	// function to set the private member email
	public function setEmail($email) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($email) > 255) {
			throw new MemberException("Member email error");
		}
		$this->_email = $email;
	}
	// function to set the private member address
	public function setAddress($address) {
		// if passed in address is not between 1 and 255 characters
		if(strlen($address) > 16777215) {
			throw new MemberException("Member address error");
		}
		$this->_address = $address;
	}
	// function to set the private member dob
	public function setDob($dob) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($dob) > 255) {
			throw new MemberException("Member dob error");
		}
		$this->_dob = $dob;
	}
	// function to set the private member doj
	public function setDoj($doj) {
		// if passed in doj is not between 1 and 255 characters
		if(strlen($doj) < 1 || strlen($doj) > 255) {
			throw new MemberException("Member doj error");
		}
		$this->_doj = $doj;
	}
	// function to set the private member idn
	public function setIDN($identification) {
		// if passed in identification is not between 1 and 255 characters
		if(strlen($identification) > 255) {
			throw new MemberException("Member identification error");
		}
		$this->_identification = $identification;
	}

	// function to set the private member status
	public function setStatus($status) {
		// if passed in status is not active or inactive
		if(!empty($status) ? (strtolower($status) !== 'active' && strtolower($status) !== 'inactive') : null) {
			throw new MemberException("Member status is not active or inactive");
		}
		$this->_status = strtolower($status);
	}

	// function to set the private member status
	public function setGender($gender) {
		// if passed in status is not Y or N
		if(strtolower($gender) !== 'male' && strtolower($gender) !== 'female' && strtolower($gender) !== '' && strtolower($gender) !== 'notdefined') {
			throw new MemberException("Member gender is not active or inactive");
		}
		$this->_gender = strtolower($gender);
	}

	// function to set the private member images array
	public function setImages($images) {
		// if passed in images is not an array
		if(!is_array($images)) {
			throw new MemberException("Member images is not an array");
		}
		$this->_images = $images;
	}
	// function to set the private member balance
	public function setBalance($balance) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($balance) < 0 || strlen($balance) > 255) {
			throw new MemberException("Member balance error");
		}
		$this->_balance = $balance;
	}
	// function to set the private member balance
	// public function setSbalance($sbalance) {
	// 	// if passed in balance is not between 1 and 255 characters
	// 	if(strlen($sbalance) < 0 || strlen($sbalance) > 255) {
	// 		throw new MemberException("Member sbalance error");
	// 	}
	// 	$this->_sbalance = $sbalance;
	// }
	// function to set the private member balance
	public function setCbalance($cbalance) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($cbalance) < 0 || strlen($cbalance) > 255) {
			throw new MemberException("Member cbalance error");
		}
		$this->_cbalance = $cbalance;
	}
	// function to set the private member balance
	public function setFbalance($fbalance) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($fbalance) < 0 || strlen($fbalance) > 255) {
			throw new MemberException("Member balance error");
		}
		$this->_fbalance = $fbalance;
	}
	// function to set the private member email
	public function setAccount($account) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($account) > 10) {
			throw new MemberException("Member account error");
		}
		$this->_account = $account;
	}
	// function to set the private member email
	public function setAttach($attach) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($attach) > 9320328) {
			throw new MemberException("Member attach error");
		}
		$this->_attach = $attach;
	}
	// function to set the private member type
	public function setType($type) {
		// if passed in type
		if(strtolower($type) !== 'group' && strtolower($type) !== 'individual') {
			throw new MemberException("Member type is not group or individual");
		}
		$this->_type = strtolower($type);
	}

	// function to set the private member employment status
	public function setEmploymentStatus($employment_status) {
		// if passed in employement status
// 		if(strlen($employment_status) < 1 || strlen($employment_status) > 50) {
// 			throw new MemberException("Member employment status error");
// 		}
		$this->_employment_status = $employment_status;
	}

	// function to set the private member gross income
	public function setGrossIncome($gross_income) {
		// if passed in gross income
// 		if(strlen($gross_income) < 1 || strlen($gross_income) > 50) {
// 			throw new MemberException("Member employment gross income error");
// 		}
		$this->_gross_income = $gross_income;
	}

	// function to set the private member marital status
	public function setMaritalStatus($marital_status) {
		// if passed in marital status
// 		if(strlen($marital_status) < 1 || strlen($marital_status) > 50) {
// 			throw new MemberException("Member marital status error");
// 		}
		$this->_marital_status = $marital_status;
	}

	// function to set the private member sacco groups
	public function setSaccoGroups($sacco_groups) {
		// if passed in gross income
		if(!is_array($sacco_groups)) {
			throw new MemberException("Member sacco is not an array");
		}
		$this->_sacco_groups = $sacco_groups;
	}
	// function to set the private member images array
	public function setNextOfKin($nextOfkin) {
		// if passed in images is not an array
		if(!is_array($nextOfkin)) {
			throw new MemberException("Member next of is not an array");
		}
		$this->_nextOfkin = $nextOfkin;
	}
	// function to set the private member images array
	public function setAccounts($accounts) {
		// if passed in images is not an array
		if(!is_array($accounts)) {
			throw new MemberException("Member accounts of is not an array");
		}
		$this->_accounts = $accounts;
	}
  // function to return member object as an array for json
	public function returnMemberAsArray() {
		$member = array();
		$member['id'] = $this->getID();
		$member['firstname'] = $this->getFirstname();
		$member['midlename'] = $this->getMidlename();
		$member['lastname'] = $this->getLastname();
		$member['contact'] = $this->getContact();
		$member['email'] = $this->getEmail();
		$member['gender'] = $this->getGender();
		$member['address'] = $this->getAddress();
		$member['dob'] = $this->getDob();
		$member['doj'] = $this->getDoj();
		$member['identification'] = $this->getIDN();
		$member['balance'] = $this->getBalance();
		// $member['sbalance'] = $this->getSbalance();
		$member['cbalance'] = $this->getCbalance();
		$member['fbalance'] = $this->getFbalance();
		$member['account'] = $this->getAccount();
		$member['type'] = $this->getType();
		$member['employment_status'] = $this->getEmploymentStatus();
		$member['gross_income'] = $this->getGrossIncome();
		$member['marital_status'] = $this->getMaritalStatus();
		$member['sacco_groups'] = $this->getSaccoGroups();
		$member['status'] = $this->getStatus();
		$member['attach'] = $this->getAttach();
		// 		$member['category'] = $this->getCategory();
		$member['images'] = $this->getImages();
		$member['nextofkin'] = $this->getNextOfKIN();
		$member['accounts'] = $this->getAccounts();

		return $member;
	}

}
