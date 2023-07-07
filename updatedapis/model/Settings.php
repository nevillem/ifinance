<?php
//  Model Object
// empty Exception class so we can catch errors
class AccountException extends Exception { }

class Account {
	// define private variables
	private $_id;
  // define private variables
  private $_name;
  // define private variables
  private $_describe;
  // define private variables
  private $_charge;
  // define private variables
  private $_balance;

  // constructor to create the country object with the instance variables already set
	public function __construct($id, $name,$describe,$charge,$balance) {
		$this->setID($id);
    $this->setName($name);
    $this->setDescribe($describe);
    $this->setCharge($charge);
    $this->setBalance($balance);
	}

  // function to return value
	public function getID() {
		return $this->_id;
	}

  // function to return value
	public function getName() {
		return $this->_name;
	}

  // function to return value
	public function getCharge() {
		return $this->_charge;
	}

  // function to return value
  public function getBalance() {
    return $this->_balance;
  }
  // function to return value
	public function getDescribe() {
		return $this->_describe;
	}



	// function to set the private value
	public function setID($id) {
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new AccountException("account ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private value
	public function setName($name) {
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new AccountException("Invalid name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private value
	public function setCharge($charge) {
		if(strlen($charge) < 1 || strlen($charge) > 255):
			throw new AccountException("Invalid charge error");
		endif;
		$this->_charge = $charge;
	}

  // function to set the private value
	public function setBalance($balance) {
		if(strlen($balance) < 1 || strlen($balance) > 255):
			throw new AccountException("Invalid balance error");
		endif;
		$this->_balance = $balance;
	}

  // function to set the private value
	public function setDescribe($describe) {
		if(strlen($describe) < 1 || strlen($describe) > 255):
			throw new AccountException("Invalid description error");
		endif;
		$this->_describe = $describe;
	}


  // function to return object as an array for json
	public function returnAccountAsArray() {
		$account = array();
		$account['id'] = $this->getID();
    $account['name'] = $this->getName();
    $account['charge'] = $this->getCharge();
    $account['balance'] = $this->getBalance();
		$account['describe'] = $this->getDescribe();

		return $account;
	}

}

// Share Model
class ShareException extends Exception { }

class Share {
	// define private variables
	private $_id;
  // define private variables
  private $_name;
  // define private variables
  private $_price;
  // define private variables
  private $_limit;


  // constructor to create the country object with the instance variables already set
	public function __construct($id, $name,$price,$limit) {
		$this->setID($id);
    $this->setName($name);
    $this->setPrice($price);
    $this->setLimit($limit);
	}

  // function to return value
	public function getID() {
		return $this->_id;
	}

  // function to return value
	public function getName() {
		return $this->_name;
	}

  // function to return value
	public function getPrice() {
		return $this->_price;
	}

  // function to return value
  public function getLimit() {
    return $this->_limit;
  }

	// function to set the private value
	public function setID($id) {
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new ShareException("account ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private value
	public function setName($name) {
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new ShareException("Invalid name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private value
	public function setPrice($price) {
		if(strlen($price) < 1 || strlen($price) > 255):
			throw new ShareException("Invalid price error");
		endif;
		$this->_price = $price;
	}

  // function to set the private value
	public function setLimit($limit) {
		if(strlen($limit) < 1 || strlen($limit) > 255):
			throw new ShareException("Invalid limit error");
		endif;
		$this->_limit = $limit;
	}


  // function to return object as an array for json
	public function returnShareAsArray() {
		$share = array();
		$share['id'] = $this->getID();
    $share['name'] = $this->getName();
    $share['price'] = $this->getPrice();
    $share['limit'] = $this->getLimit();

		return $share;
	}

}

// Loan Model
class LoanException extends Exception { }

class Loan {
	// define private variables
	private $_id;
  // define private variables
  private $_name;
  // define private variables
  private $_interest;
	// define private variables
	private $_period;
  // define private variables
  private $_penalty;
	// define private variables
	private $_frequency;
	// define private variables
	private $_fee;
	// define private variables
	private $_notes;


  // constructor to create the country object with the instance variables already set
	public function __construct($id, $name,$interest,$period,$penalty,$frequency,$fee,$notes) {
		$this->setID($id);
    $this->setName($name);
    $this->setInterest($interest);
		$this->setPeriod($period);
		$this->setPenalty($penalty);
		$this->setFrequency($frequency);
		$this->setFee($fee);
    $this->setNotes($notes);
	}

  // function to return value
	public function getID() {
		return $this->_id;
	}

  // function to return value
	public function getName() {
		return $this->_name;
	}

  // function to return value
	public function getInterest() {
		return $this->_interest;
	}

  // function to return value
  public function getPeriod() {
    return $this->_period;
  }
	// function to return value
	public function getPenalty() {
		return $this->_penalty;
	}
	// function to return value
	public function getFrequency() {
		return $this->_frequency;
	}
	// function to return value
	public function getFee() {
		return $this->_fee;
	}
	// function to return value
	public function getNotes() {
		return $this->_notes;
	}

	// function to set the private value
	public function setID($id) {
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new LoanException("loan ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private value
	public function setName($name) {
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new LoanException("loan name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private value
	public function setInterest($interest) {
		if(strlen($interest) < 1 || strlen($interest) > 255):
			throw new LoanException("loan interest error");
		endif;
		$this->_interest = $interest;
	}
	// function to set the private value
	public function setPenalty($penalty) {
		if(strlen($penalty) < 1 || strlen($penalty) > 255):
			throw new LoanException("loan penalty error");
		endif;
		$this->_penalty = $penalty;
	}

  // function to set the private value
	public function setFrequency($frequency) {
		if(strtolower($frequency) !== 'monthly' && strtolower($frequency) !== 'weekly'
		&& strtolower($frequency) !== 'daily' && strtolower($frequency) !== 'onetime'):
			throw new LoanException("loan frequency error");
		endif;
		$this->_frequency = $frequency;
	}

	// function to set the private value
	public function setPeriod($period) {
		if((!is_numeric($period)) && strlen($period) < 1 || strlen($period) > 255):
			throw new LoanException("loan period error");
		endif;
		$this->_period = $period;
	}
	// function to set the private value
	public function setFee($fee) {
		if((!is_numeric($fee)) && strlen($fee) < 1 || strlen($fee) > 255):
			throw new LoanException("loan fee error");
		endif;
		$this->_fee = $fee;
	}

	// function to set the private value
	public function setNotes($notes) {
		if(strlen($notes) > 255):
			throw new LoanException("loan notes error");
		endif;
		$this->_notes= $notes;
	}


  // function to return object as an array for json
	public function returnLoanAsArray() {
		$loan = array();
		$loan['id'] = $this->getID();
    $loan['name'] = $this->getName();
    $loan['interest'] = $this->getInterest();
		$loan['period'] = $this->getPeriod();
		$loan['penalty'] = $this->getPenalty();
		$loan['frequency'] = $this->getFrequency();
		$loan['fee'] = $this->getFee();
    $loan['notes'] = $this->getNotes();

		return $loan;
	}

}

// Capital Model
class CapitalException extends Exception { }

class Capital {
	// define private variables
	private $_id;
  // define private variables
  private $_name;
  // define private variables
  private $_date;
  // define private variables
  private $_amount;


  // constructor to create the country object with the instance variables already set
	public function __construct($id, $name,$amount,$date) {
		$this->setID($id);
    $this->setName($name);
    $this->setDate($date);
		$this->setAmount($amount);
	}

  // function to return value
	public function getID() {
		return $this->_id;
	}

  // function to return value
	public function getName() {
		return $this->_name;
	}

  // function to return value
	public function getDate() {
		return $this->_date;
	}

  // function to return value
  public function getAmount() {
    return $this->_amount;
  }

	// function to set the private value
	public function setID($id) {
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new CapitalException("capital ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private value
	public function setName($name) {
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new CapitalException("capital name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private value
	public function setAmount($amount) {
		if(strlen($amount) < 1 || strlen($amount) > 255):
			throw new CapitalException("capital amount error");
		endif;
		$this->_amount = $amount;
	}

  // function to set the private value
	public function setDate($date) {
		if(strlen($date) < 1 || strlen($date) > 255):
			throw new CapitalException("capital date error");
		endif;
		$this->_date = $date;
	}


  // function to return object as an array for json
	public function returnCapitalAsArray() {
		$capital = array();
		$capital['id'] = $this->getID();
    $capital['name'] = $this->getName();
    $capital['amount'] = $this->getAmount();
    $capital['date'] = $this->getDate();

		return $capital;
	}

}

// Inpense Model
class InpenseException extends Exception { }

class Inpense {
	// define private variables
	private $_id;
  // define private variables
  private $_name;
  // define private variables
  private $_type;

  // constructor to create the country object with the instance variables already set
	public function __construct($id, $name,$type) {
		$this->setID($id);
    $this->setName($name);
    $this->setType($type);
	}

  // function to return value
	public function getID() {
		return $this->_id;
	}

  // function to return value
	public function getName() {
		return $this->_name;
	}

  // function to return value
	public function getType() {
		return $this->_type;
	}

	// function to set the private value
	public function setID($id) {
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)):
			throw new InpenseException("Inpense ID error");
		endif;
		$this->_id = $id;
	}

  // function to set the private value
	public function setName($name) {
		if(strlen($name) < 1 || strlen($name) > 255):
			throw new InpenseException("Inpense name error");
		endif;
		$this->_name = $name;
	}

  // function to set the private value
	public function setType($type) {
		if(strtolower($type) !== 'expense' && strtolower($type) !== 'income'):
			throw new InpenseException("Inpense amount error");
		endif;
		$this->_type = $type;
	}

  // function to return object as an array for json
	public function returnInpenseAsArray() {
		$inpense = array();
		$inpense['id'] = $this->getID();
    $inpense['name'] = $this->getName();
    $inpense['type'] = $this->getType();

		return $inpense;
	}

}
