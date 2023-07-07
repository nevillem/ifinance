<?php
// Task Model Object

// empty NextOfKinException class so we can catch task errors
class MemberAccountsException extends Exception { }

class MemberAccounts {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store  member id
	private $_memberid;
	// define variable to store  firstname
	private $_accounts_attach;
	// // define variable to store gender
	private $_date;

  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$memberid, $accounts_attach = array()) {
		$this->setID($id);
		$this->setMemberID($memberid);
		$this->setAccountsAttached($accounts_attach);
		$this->setDateOpened($date);
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
	public function getAccountAttached() {
		return $this->_accounts_attach;
	}
	// function to return account date opened
	public function getDateOpened() {
	   return $this->_date;
	}
	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new MemberAccountsException("member account ID error");
		}
		$this->_id = $id;
	}

	// function to set the private Member ID
	public function setMemberID($memberid) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($memberid !== null) && (!is_numeric($memberid) || $memberid <= 0 || $memberid > 9223372036854775807 || $this->_memberid !== null)) {
			throw new MemberAccountsException("Member ID error");
		}
		$this->_memberid = $memberid;
	}

  // function to set the private member images array
	public function setAccountsAttached($accounts_attach) {
		// if passed in images is not an array
		if(!is_array($accounts_attach)) {
			throw new MemberAccountsException("member attached accounts is not an array");
		}
		$this->_accounts_attach = $accounts_attach;
	}
  public function setDateOpened($date) {
    // if passed in title is not between 1 and 255 characters
    if(strlen($date) > 255) {
      throw new MemberAccountsException("date opened error");
    }
    $this->_date = $date;
  }

  // function to return member object as an array for json
	public function returnAccountsAsArray() {
		$accountAttach = array();
		$accountAttach['id'] = $this->getID();
		$accountAttach['member'] = $this->getMemberID();
		$accountAttach['account'] = $this->getAccount();
		$accountAttach['accountname'] = $this->getAccountName();
		$accountAttach['dateopened'] = $this->getDateOpened();

		return $accountAttach;
	}

}
