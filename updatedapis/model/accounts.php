<?php
// Task Model Object

// empty NextOfKinException class so we can catch task errors
class AccountsException extends Exception { }

class Accounts {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store  member id
	private $_memberid;
	// define variable to store  firstname
	private $_account;
	private $_accountname;
	// // define variable to store gender
	private $_date;

  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$memberid, $account, $accountname, $date) {
		$this->setID($id);
		$this->setMemberID($memberid);
		$this->setAccountID($account);
		$this->setAccountName($accountname);
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
	public function getAccount() {
		return $this->_account;
	}
	// function to return member firstname
		public function getAccountName() {
			return $this->_accountname;
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
			throw new AccountsException("next of kin ID error");
		}
		$this->_id = $id;
	}

	// function to set the private Member ID
	public function setMemberID($memberid) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($memberid !== null) && (!is_numeric($memberid) || $memberid <= 0 || $memberid > 9223372036854775807 || $this->_memberid !== null)) {
			throw new AccountsException("Member ID error");
		}
		$this->_memberid = $memberid;
	}
	// function to set the private Member ID
	public function setAccountID($account) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($account !== null) && (!is_numeric($account) || $account <= 0 || $account > 9223372036854775807
		 || $this->_account !== null)) {
			throw new AccountsException("Account ID error");
		}
		$this->_account = $account;
	}
	// function to set the private member email
	public function setAccountName($accountname) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($accountname) > 255) {
			throw new AccountsException("account name error");
		}
		$this->_accountname = $accountname;
	}
	// function to set the private member dob
	public function setDateOpened($date) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($date) > 255) {
			throw new AccountsException("date opened error");
		}
		$this->_date = $date;
	}

  // function to return member object as an array for json
	public function returnAccountsAsArray() {
		$account = array();
		$account['id'] = $this->getID();
		$account['member'] = $this->getMemberID();
		$account['account'] = $this->getAccount();
		$account['accountname'] = $this->getAccountName();
		$account['dateopened'] = $this->getDateOpened();

		return $account;
	}

}
