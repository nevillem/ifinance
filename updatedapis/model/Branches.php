<?php
// Branch Model Object
// empty Branch Exception class so we can catch errors
class BranchException extends Exception { }

class Branch {
	// define private variables
	// define variable to store branch id number
	private $_id;
  // define variable to store branch name
  private $_name;
	// define variable to store branch address
	private $_address;
  // define variable to store branch code
  private $_code;
  // define variable to store branch status
  private $_status;


  // constructor to create the branch object with the instance variables already set
	public function __construct($id, $name, $address, $code, $status) {
		$this->setID($id);
		$this->setName($name);
		$this->setAddress($address);
    $this->setCode($code);
		$this->setStatus($status);
	}

  // function to return branch ID
	public function getID() {
		return $this->_id;
	}

  // function to return branch name
	public function getName() {
		return $this->_name;
	}

  // function to return branch name
	public function getAddress() {
		return $this->_address;
	}

  // function to return branch code
	public function getCode() {
		return $this->_code;
	}

  // function to return branch status
	public function getStatus() {
		return $this->_status;
	}

	// function to set the private branch ID
	public function setID($id) {
		// if passed in branch ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new BranchException("Branch ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private branch name
	public function setName($name) {
		// if passed in branch name is not between 1 and 255 characters
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new BranchException("Invalid branch name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private address
  public function setAddress($address) {
    // if passed in address is not between 1 and 16777215 characters
    if(strlen($address) == 0 || strlen($address) > 16777215):
      throw new BranchException("branch address error");
    endif;
    $this->_address = $address;
  }

  // function to set the private code
  public function setCode($code) {
    // if passed in code is not between 1 and 4 characters
    if(strlen($code) < 0 || strlen($code) > 4 || preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $code)):
      throw new BranchException("branch code error");
    endif;
    $this->_code = $code;
  }

  // function to set the private status type
  public function setStatus($status) {
    // if passed in branch is active or inactive
    if(strtolower($status) !== 'active' && strtolower($status) !== 'inactive'):
      throw new BranchException("branch status error");
    endif;
    $this->_status = $status;
  }

  // function to return branches object as an array for json
	public function returnBranchAsArray() {
		$branch = array();
		$branch['id'] = $this->getID();
		$branch['name'] = $this->getName();
		$branch['address'] = $this->getAddress();
    $branch['code'] = $this->getCode();
		$branch['status'] = $this->getStatus();

		return $branch;
	}

}
