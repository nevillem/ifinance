<?php
// Task Model Object

// empty NextOfKinException class so we can catch task errors
class NextOfKinException extends Exception { }

class NextOfKin {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store  member id
	private $_memberid;
	// define variable to store  firstname
	private $_firstname;
	// define variable to store lastname
		private $_midlename;
	// define variable to store contact
	private $_lastname;
	// define variable to store contact
	private $_contact;
	// define variable to store relationship
	private $_relationship;
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
	// define variable to store inheritance %
	private $_inheritance;



  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$memberid, $firstname,$midlename, $lastname, $contact,
	$gender, $email, $address,
	$dob, $doj, $identification, $relationship, $inheritance) {
		$this->setID($id);
		$this->setMemberID($memberid);
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
		$this->setRelationship($relationship);
		$this->setInheritance($inheritance);
	}

  // function to return nextofkin ID
	public function getID() {
		return $this->_id;
	}
	// function to return member ID
	public function getMemberID() {
		return $this->_memberid;
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


  // function to return relation with the member
	public function getRelationship() {
		return $this->_relationship;
	}
	// function to return nextofkin inheritance %
	public function getInheritance() {
		return $this->_inheritance;
	}


	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new NextOfKinException("next of kin ID error");
		}
		$this->_id = $id;
	}

	// function to set the private Member ID
	public function setMemberID($memberid) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($memberid !== null) && (!is_numeric($memberid) || $memberid <= 0 || $memberid > 9223372036854775807 || $this->_memberid !== null)) {
			throw new NextOfKinException("Member ID error");
		}
		$this->_memberid = $memberid;
	}

  // function to set the private firstname
	public function setFirstname($firstname) {
		// if passed in firstname is not between 1 and 255 characters
		if(strlen($firstname) < 1 || strlen($firstname) > 255) {
			throw new NextOfKinException("Next of kin firstname error");
		}
		$this->_firstname = $firstname;
	}

	// function to set the private midlename
	public function setMidlename($midlename) {
		// if passed in lastname is not between 1 and 255 characters
// 		if(strlen($midlename) < 1 || strlen($midlename) > 255) {
// 			throw new NextOfKinException("member midlename error");
// 		}
		$this->_midlename = $midlename;
	}
	// function to set the private lastname
	public function setLastname($lastname) {
		// if passed in lastname is not between 1 and 255 characters
		if(strlen($lastname) < 1 || strlen($lastname) > 255) {
			throw new NextOfKinException("Next of kin lastname error");
		}
		$this->_lastname = $lastname;
	}
	// function to set the private member contact
	public function setContact($contact) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($contact) < 1 || strlen($contact) > 255) {
			throw new NextOfKinException("Next of kin contact error");
		}
		$this->_contact = $contact;
	}
	// function to set the private member email
	public function setEmail($email) {
		// if passed in email is not between 1 and 255 characters
		// if(strlen($email) > 255) {
		// 	throw new NextOfKinException("Next of kin email error");
		// }
		$this->_email = $email;
	}
	// function to set the private member address
	public function setAddress($address) {
		// if passed in address is not between 1 and 255 characters
		// if(strlen($address) > 16777215) {
		// 	throw new NextOfKinException("Next of kin address error");
		// }
		$this->_address = $address;
	}
	// function to set the private member dob
	public function setDob($dob) {
		// if passed in title is not between 1 and 255 characters
		// if(strlen($dob) > 255) {
		// 	throw new NextOfKinException("Next of kin dob error");
		// }
		$this->_dob = $dob;
	}
	// function to set the private member doj
	public function setDoj($doj) {
		// if passed in doj is not between 1 and 255 characters
		// if(strlen($doj) < 1 || strlen($doj) > 255) {
		// 	throw new NextOfKinException("Member doj error");
		// }
		$this->_doj = $doj;
	}
	// function to set the private member idn
	public function setIDN($identification) {
		// if passed in identification is not between 1 and 255 characters
		// if(strlen($identification) > 255) {
		// 	throw new NextOfKinException("Next of kin identification error");
		// }
		$this->_identification = $identification;
	}

	// function to set the private member status
	public function setGender($gender) {
		// if passed in status is not Y or N
		// if(strtolower($gender) !== 'male' && strtolower($gender) !== 'female' && strtolower($gender) !== '' && strtolower($gender) !== 'notdefined') {
		// 	throw new NextOfKinException("MemNext of kinber gender is not active or inactive");
		// }
		$this->_gender = strtolower($gender);
	}


	// function to set the private relationship
	public function setRelationship($relationship) {
		if(strlen($relationship) > 9320328) {
			throw new NextOfKinException("Relation with the Next of kin error");
		}
		$this->_relationship = $relationship;
	}

	// function to set the private relationship
	public function setInheritance($inheritance) {
	// if (strlen($inheritance) < 1 || strlen($inheritance) > 255) {
	// 		throw new NextOfKinException("Next of kin Inheritance % error");
	// 	}
		$this->_inheritance = $inheritance;
	}


  // function to return member object as an array for json
	public function returnNextOFKinAsArray() {
		$member = array();
		$member['id'] = $this->getID();
		$member['member'] = $this->getMemberID();
		$member['firstname'] = $this->getFirstname();
		$member['midlename'] = $this->getMidlename();
		$member['lastname'] = $this->getLastname();
		$member['contact'] = $this->getContact();
		$member['email'] = $this->getEmail();
		$member['gender'] = $this->getGender();
		$member['address'] = $this->getAddress();
		$member['dob'] = $this->getDob();
		$member['doj'] = $this->getDoj();
		// $member['email'] = $this->getEmail();
		$member['identification'] = $this->getIDN();
		$member['relationship'] = $this->getRelationship();
		$member['inheritance'] = $this->getInheritance();

		return $member;
	}

}
