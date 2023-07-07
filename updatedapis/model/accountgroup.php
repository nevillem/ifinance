<?php
// Task Model Object

// empty NextOfKinException class so we can catch task errors
class AccountsGroupException extends Exception { }

class AccountGroup {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store  member id
	private $_name;
	// define variable to store  firstname
	private $_code;

  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$name,$code) {
		$this->setID($id);
		$this->setName($name);
		$this->setCode($code);
	}

  // function to return nextofkin ID
	public function getID() {
		return $this->_id;
	}
	// function to return name
	public function getName() {
		return $this->_name;
	}
	// function to return code
	public function getCode() {
		return $this->_code;
	}

	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new AccountsGroupException("account group ID error");
		}
		$this->_id = $id;
	}

	// function to set the private member email
	public function setName($name) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($name) > 255) {
			throw new AccountsGroupException("group name error");
		}
		$this->_name = $name;
	}
	// function to set the private member dob
	public function setCode($code) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($code) > 50) {
			throw new AccountsGroupException("account group code error");
		}
		$this->_code = $code;
	}

  // function to return member object as an array for json
	public function returnAccountGroupAsArray() {
		$accountgroup = array();
		$accountgroup['id'] = $this->getID();
		$accountgroup['code'] = $this->getCode();
		$accountgroup['name'] = $this->getName();

		return $accountgroup;
	}

}
class SubAccountsGroupException extends Exception { }

class SubAccountGroup {
	// define private variables

	// define variable to store  SubAccountGroup id
	private $_id;
	// define variable to store  SubAccountGroup name
	private $_name;
	// define variable to store  SubAccountGroup branch
	private $_branch;
	// define variable to store  SubAccountGroup branch
	private $_accountgroup;
	// define variable to store  SubAccountGroup code
	private $_code;

  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$name,$accountgroup,$branch,$code) {
		$this->setID($id);
		$this->setName($name);
		$this->setBranch($branch);
		$this->setAccountGroup($accountgroup);
		$this->setCode($code);
	}

  // function to return nextofkin ID
	public function getID() {
		return $this->_id;
	}
	// function to return name
	public function getName() {
		return $this->_name;
	}
	public function getBranch() {
		return $this->_branch;
	}
	public function getAcountgroup() {
		return $this->_accountgroup;
	}
	// function to return code
	public function getCode() {
		return $this->_code;
	}
	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new SubAccountsGroupException("sub account group ID error");
		}
		$this->_id = $id;
	}

	// function to set the private member email
	public function setName($name) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($name) > 255) {
			throw new SubAccountsGroupException("sub account group name error");
		}
		$this->_name = $name;
	}
	// function to set the private member dob
	public function setCode($code) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($code) > 50) {
			throw new SubAccountsGroupException("sub account group code error");
		}
		$this->_code = $code;
	}
	// function to set the private Member ID
	public function setBranch($branch) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		// if(($branch !== null) && (!is_numeric($branch) || $branch <= 0 || $branch > 9223372036854775807
		//  || $this->_branch !== null)) {
		// 	throw new SubAccountsGroupException("sub account branch ID error");
		// }
		if(strlen($branch) > 255) {
				throw new SubAccountsGroupException("sub account branch ID error");
		}
		$this->_branch = $branch;
	}
	// function to set the private Member ID
	public function setAccountGroup($accountgroup) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		// if(($accountgroup !== null) && (!is_numeric($accountgroup) || $accountgroup <= 0 || $accountgroup > 9223372036854775807
		//  || $this->_accountgroup !== null)) {
		// 	throw new SubAccountsGroupException("sub Account group ID error");
		// }
		if(strlen($accountgroup) > 255) {
				throw new SubAccountsGroupException("sub Account group ID error");
		}
		$this->_accountgroup = $accountgroup;
	}
  // function to return member object as an array for json
	public function returnSubAccountGroupAsArray() {
		$subaccountgroups = array();
		$subaccountgroups['id'] = $this->getID();
		$subaccountgroups['code'] = $this->getCode();
		$subaccountgroups['subaccount'] = $this->getName();
		$subaccountgroups['accountgroup'] = $this->getAcountgroup();
		$subaccountgroups['saccobranch'] = $this->getBranch();

		return $subaccountgroups;
	}

}

class AccountsException extends Exception { }

class Accounts {
	// define private variables

	// define variable to  id
	private $_id;
	// define variable to name
	private $_name;
	// define variable to status
	private $_status;
	// define variable to store  Sopening balance
	private $_openingbalance;
	// define variable to code
	private $_code;
	// define variable to code
	private $_subaccount;
	private $_accountgroup;
	private $_account_types;
  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$name,$status,$openingbalance, $account_types,$code, $subaccount,$accountgroup) {
		$this->setID($id);
		$this->setName($name);
		$this->setStatus($status);
		$this->setOpeningBalance($openingbalance);
		$this->setCode($code);
		$this->setSubaccount($subaccount);
		$this->setAccountGroup($accountgroup);
		$this->setAccountType($account_types);
	}

  // function to return nextofkin ID
	public function getID() {
		return $this->_id;
	}
	// function to return name
	public function getName() {
		return $this->_name;
	}
	public function getStatus() {
		return $this->_status;
	}
	public function getOpeningBalance() {
		return $this->_openingbalance;
	}
	// function to return code
	public function getCode() {
		return $this->_code;
	}
	// function to return code
	public function getSubaccounts() {
		return $this->_subaccount;
	}
	public function getAccountGroup(){
		return $this->_accountgroup;
	}
	public function getAccountTypes(){
		return $this->_account_types;
	}
	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new AccountsException("account ID error");
		}
		$this->_id = $id;
	}

	// function to set the private member email
	public function setName($name) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($name) > 255) {
			throw new AccountsException("account name error");
		}
		$this->_name = $name;
	}
	// function to set the private member email
	public function setStatus($status) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($status) > 255) {
			throw new AccountsException("account status error");
		}
		$this->_status = $status;
	}
	public function setOpeningBalance($openingbalance) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($openingbalance) < 1 || strlen($openingbalance) > 255) {
			throw new AccountsException("opening balance error");
		}
		$this->_openingbalance = $openingbalance;
	}
	// function to set the private member dob
	public function setCode($code) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($code) > 50) {
			throw new AccountsException("account code error");
		}
		$this->_code = $code;
	}
	// function to set the private Member ID
	public function setSubaccount($subaccount) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		// if(($branch !== null) && (!is_numeric($branch) || $branch <= 0 || $branch > 9223372036854775807
		//  || $this->_branch !== null)) {
		// 	throw new SubAccountsGroupException("sub account branch ID error");
		// }
		if(strlen($subaccount) > 255) {
				throw new AccountsException("sub account error");
		}
		$this->_subaccount = $subaccount;
	}
	// function to set the private Member ID
	public function setAccountGroup($accountgroup) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		// if(($branch !== null) && (!is_numeric($branch) || $branch <= 0 || $branch > 9223372036854775807
		//  || $this->_branch !== null)) {
		// 	throw new SubAccountsGroupException("sub account branch ID error");
		// }
		if(strlen($accountgroup) > 255) {
				throw new AccountsException("account group error");
		}
		$this->_accountgroup = $accountgroup;
	}
	public function setAccountType($account_types) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($account_types) > 255) {
			throw new AccountsException("account type error");
		}
		$this->_account_types = $account_types;
	}
  // function to return member object as an array for json
	public function returnAccountsAsArray() {
		$accounts = array();
		$accounts['id'] = $this->getID();
		$accounts['code'] = $this->getCode();
		$accounts['account'] = $this->getName();
		$accounts['account_group'] = $this->getAccountGroup();
		$accounts['status'] = $this->getStatus();
		$accounts['openingbalance'] = $this->getOpeningBalance();
		$accounts['accounttype'] = $this->getAccountTypes();
		$accounts['subaccount'] = $this->getSubaccounts();

		return $accounts;
	}

}

class WithdrawException extends Exception { }

class WithdrawSettings {
	// define private variables

	// define variable to  id
	private $_id;
	// define variable to name
	private $_account;
	// define variable to store  Sopening balance
	private $_minimumbalance;
	// define variable to code
	private $_amountfrom;
	// define variable to code
	private $_amountto;
	private $_charge;
	private $_modeofdeduction;
  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$account,$minimumbalance,$amountfrom, $amountto,$charge,$modeofdeduction) {
		$this->setID($id);
		$this->setAccounts($account);
		$this->setMinimumBalance($minimumbalance);
		$this->setAmountFrom($amountfrom);
		$this->setAmountTo($amountto);
		$this->setWithdrawcharge($charge);
		$this->setModeofdeduction($modeofdeduction);
	}

  // function to return nextofkin ID
	public function getID() {
		return $this->_id;
	}
	// function to return name
	public function getAccounts() {
		return $this->_account;
	}
	public function getMinimumBalance() {
		return $this->_minimumbalance;
	}
	public function getAmountTo() {
		return $this->_amountto;
	}
	public function getAmountFrom() {
		return $this->_amountfrom;
	}
	// function to return code
	public function getModeofdeduction() {
		return $this->_modeofdeduction;
	}
	// function to return code
	public function getWithdrawcharge() {
		return $this->_charge;
	}
	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new WithdrawException("withdraw ID error");
		}
		$this->_id = $id;
	}

	// function to set the private member email
	public function setAccounts($account) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($account) > 255) {
			throw new WithdrawException("account error");
		}
		$this->_account = $account;
	}
	// function to set the private member email
	public function setMinimumBalance($minimumbalance) {
		// if passed in email is not between 1 and 255 characters
		if(!is_numeric($minimumbalance) || $minimumbalance < 0 || $minimumbalance > 9223372036854775807) {
			throw new WithdrawException("minimum balance to error");
		}
		$this->_minimumbalance = $minimumbalance;
	}
	public function setAmountTo($amountto) {
		// if passed in email is not between 1 and 255 characters
		if(!is_numeric($amountto) || $amountto <= 0 || $amountto > 9223372036854775807) {
			throw new WithdrawException("amount to error");
		}
		$this->_amountto = $amountto;
	}
	public function setAmountFrom($amountfrom) {
		// if passed in email is not between 1 and 255 characters
		if(!is_numeric($amountfrom) && $amountfrom <0 || $amountfrom > 9223372036854775807) {
			throw new WithdrawException("amount from error");
		}
		$this->_amountfrom = $amountfrom;
	}
	public function setWithdrawcharge($charge) {
		// if passed in email is not between 1 and 255 characters
		if(!is_numeric($charge) && $charge <0){
			throw new WithdrawException("withdraw charge error");
		}
		$this->_charge = $charge;
	}
	// function to set the private Member ID
	public function setModeofdeduction($modeofdeduction) {
		if(strlen($modeofdeduction) > 255) {
				throw new WithdrawException("deduction mode error");
		}
		$this->_modeofdeduction = $modeofdeduction;
	}

  // function to return member object as an array for json
	public function returnWithdrawSettingAsArray() {
		$sithdrawsettings = array();
		$sithdrawsettings['id'] = $this->getID();
		$sithdrawsettings['account'] = $this->getAccounts();
		$sithdrawsettings['minbalance'] = $this->getMinimumBalance();
		$sithdrawsettings['charge'] = $this->getWithdrawcharge();
		$sithdrawsettings['amountfrom'] = $this->getAmountFrom();
		$sithdrawsettings['amountto'] = $this->getAmountTo();
		$sithdrawsettings['modeofdeduction'] = $this->getModeofdeduction();

		return $sithdrawsettings;
	}

}

class FixedDepositSettingException extends Exception { }

class FixedDepositSetting {
	// define private variables

	// define variable to  id
	private $_id;
	// define variable to name
	private $_account;
	// define variable to store  Sopening balance
	private $_rate;
	// define variable to code
	private $_interest_expense_account;
	// define variable to code
	private $_interest_calc_mode;
	private $_interest_payable_acc;
	private $_interest_accumulation_interval;
  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id,$account,$rate,$interest_expense_account, $interest_calc_mode,$interest_payable_acc,
	$interest_accumulation_interval) {
		$this->setID($id);
		$this->setAccount($account);
		$this->setAnnualRate($rate);
		$this->setExpenseAccount($interest_expense_account);
		$this->setInterestCalcMode($interest_calc_mode);
		$this->setInterestPayableAcc($interest_payable_acc);
		$this->setInterestAccumulationInterval($interest_accumulation_interval);
	}

  // function to return nextofkin ID
	public function getID() {
		return $this->_id;
	}
	// function to return name
	public function getAccount() {
		return $this->_account;
	}
	public function getAnnualRate() {
		return $this->_rate;
	}
	public function getInterestExpenseAccount() {
		return $this->_interest_expense_account;
	}
	public function getInterestCalcMode() {
		return $this->_interest_calc_mode;
	}
	// function to return code
	public function getInterestPayableAcc() {
		return $this->_interest_payable_acc;
	}
	public function getInterestAccumulationInterval() {
			return $this->_interest_accumulation_interval;
	}
	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new FixedDepositSettingException("fixed deposit setting ID error");
		}
		$this->_id = $id;
	}

	// function to set the private member email
	public function setAccount($account) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($account) > 255) {
			throw new FixedDepositSettingException("account setting error");
		}
		$this->_account = $account;
	}
	// function to set the private member email
	public function setAnnualRate($rate) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($rate) > 255) {
			throw new FixedDepositSettingException("minimum balance to ID error");
		}
		$this->_rate = $rate;
	}
	// function to set the private rate
	public function setExpenseAccount($interest_expense_account) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($interest_expense_account) > 255) {

			throw new FixedDepositSettingException("minimum balance to ID error");
		}
		$this->_interest_expense_account = $interest_expense_account;
	}
	public function setInterestCalcMode($interest_calc_mode) {
		// if passed in email is not between 1 and 255 characters
	if(strlen($interest_calc_mode) > 255) {

			throw new FixedDepositSettingException("amount to ID error");
		}
		$this->_interest_calc_mode = $interest_calc_mode;
	}
	public function setInterestPayableAcc($interest_payable_acc) {
		// if passed in email is not between 1 and 255 characters
			if(strlen($interest_payable_acc) > 255) {
			throw new FixedDepositSettingException("interest payable account from error");
		}
		$this->_interest_payable_acc = $interest_payable_acc;
	}
	// function to set the private member dob
	public function setInterestAccumulationInterval($interest_accumulation_interval) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($interest_accumulation_interval) > 255) {
			throw new FixedDepositSettingException(" interest accumulation interval error");
		}
		$this->_interest_accumulation_interval = $interest_accumulation_interval;
	}

  // function to return member object as an array for json
	public function returnFixedDepositSettingAsArray() {
		$fixeddepositssettings = array();
		$fixeddepositssettings['id'] = $this->getID();
		$fixeddepositssettings['account'] = $this->getAccount();
		$fixeddepositssettings['annualinterest'] = $this->getAnnualRate();
		//interest calculation mode
		$fixeddepositssettings['interestcalculationmode'] = $this->getInterestCalcMode();
		$fixeddepositssettings['interestexpenseaccount'] = $this->getInterestExpenseAccount();
		$fixeddepositssettings['interestpayableaccount'] = $this->getInterestPayableAcc();
		$fixeddepositssettings['howinteresisearned'] = $this->getInterestAccumulationInterval();

		return $fixeddepositssettings;
	}
}
