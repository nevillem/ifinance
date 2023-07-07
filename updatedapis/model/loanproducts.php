<?php
// Task Model Object

// empty MemberException class so we can catch task errors
class LoanProductSettingsException extends Exception { }

class LoanProductSettings  {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store  firstname
	private $_product_name;
	// define variable to store lastname
		private $_loan_type;
	// define variable to store contact
	private $_install_payment;
	// define variable to store contact
	private $_loan_rate_type;
	// define variable to store gender
	private $_interest_rate;
	// define variable to store gender
	private $_loan_processing_fees;
	// define variable to store address
	private $_minmum_amount;
	// define variable to store dob
	private $_maxmum_amount;
	// define variable to store doj
	private $_number_of_guarantors;
	// define variable to store status
	private $_can_client_be_self_guarantor;
	// define variable to images array
	private $_deduct_installment_beofore_disbursment;
	// define variable to member type
	private $_does_interest_change_defaulted;
	// define variable to volunter account balance
	private $_new_interest_rate;
	//must have security
	private $_must_have_security;
	//define employment statua
	private $_charge_penalt;

  // constructor to create the member object with the instance variables already set
	// default images to empty array if nothing passed in
	public function __construct($id, $product_name,$loan_type, $install_payment, $loan_rate_type, $interest_rate,
	 $loan_processing_fees, $minmum_amount, $maxmum_amount, $number_of_guarantors,
	 $can_client_be_self_guarantor, $deduct_installment_beofore_disbursment,
	  $does_interest_change_defaulted,$new_interest_rate,$must_have_security,$charge_penalt) {
		$this->setID($id);
		$this->setProductname($product_name);
		$this->setLoanType($loan_type);
		$this->setInstallPayment($install_payment);
		$this->setLoanRateType($loan_rate_type);
		$this->setIneterestRate($interest_rate);
		$this->setLoanProcessingFees($loan_processing_fees);
		$this->setMinmumAmount($minmum_amount);
		$this->seMaximumAmount($maxmum_amount);
		$this->setNumberOfGuarantors($number_of_guarantors);
		$this->setCanClientBeSelfGuarantor($can_client_be_self_guarantor);
		$this->setDeductInstallmentBeforeDesbursment($deduct_installment_beofore_disbursment);
		$this->setDoesInterestChangeDefaulted($does_interest_change_defaulted);
		$this->setNewInternetRate($new_interest_rate);
		$this->setMustHaveSecurity($must_have_security);
		$this->setChargePenalt($charge_penalt);
	}

  // function to return member ID
	public function getID() {
		return $this->_id;
	}

  // function to return member firstname
	public function getProductName() {
		return $this->_product_name;
	}
	// function to return member lmidlename
	public function getLoanType() {
		return $this->_loan_type;
	}
	// function to return member lastname
	public function getInstallPayment() {
		return $this->_install_payment;
	}
	// function to return member lastname
	public function getLoanRateType() {
		return $this->_loan_rate_type;
	}
	// function to return member contact
	public function getInterestRate() {
		return $this->_interest_rate;
	}

	// function to return member gender
	public function getLoanProcessingFees() {
		return $this->_loan_processing_fees;
	}
	public function getMinimumAmount() {
		return $this->_minmum_amount;
	}
	public function getMaxmumAmount(){
		return $this-> _maxmum_amount;
	}
	// function to return number of guarantors
	public function getNumberOfGuarantors() {
		return $this->_number_of_guarantors;
	}

  // function to return memeber identification
	public function getClientBeSelfGuarantor() {
		return $this->_can_client_be_self_guarantor;
	}
	public function getDeductInstallmentBeforeDesbursment() {
		return $this->_deduct_installment_beofore_disbursment;
	}
	public function getDoesInterestChangeDefault() {
		return $this->_does_interest_change_defaulted;
	}
	public function getNewInterestRate() {
		return $this->_new_interest_rate;
	}
	public function getMustHaveSecurity() {
		return $this->_must_have_security;
	}
	public function getChargePenalt() {
		return $this->_charge_penalt;
	}
	// function to set the private Member ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new LoanProductSettingsException("Member ID error");
		}
		$this->_id = $id;
	}

  // function to set the private firstname
	public function setProductname($product_name) {
		// if passed in firstname is not between 1 and 255 characters
		if(strlen($product_name) < 1 || strlen($product_name) > 255) {
			throw new LoanProductSettingsException("product name error");
		}
		$this->_product_name = $product_name;
	}

	// function to set the private lastname
	public function setLoanType($loan_type) {
		// if passed in lastname is not between 1 and 255 characters
		if(strlen($loan_type) < 1 || strlen($loan_type) > 255) {
			throw new LoanProductSettingsException("loan type error");
		}
		$this->_loan_type = $loan_type;
	}
	// function to set the private  loan installemt payment settings
	public function setInstallPayment($install_payment) {
		// if passed in title is not between 1 and 255 characters
		if(strlen($install_payment) < 1 || strlen($install_payment) > 255) {
			throw new LoanProductSettingsException("installment payment error");
		}
		$this->_install_payment = $install_payment;
	}
	// function to set the private  loan installemt payment settings
	public function setLoanRateType($loan_rate_type) {
		// function to set the private member status
			// if passed in status is not Y or N
			if(strtolower($loan_rate_type) !== 'straight line' && strtolower($loan_rate_type) !== 'reducing balance') {
				throw new LoanProductSettingsException("Member gender is not active or inactive");
			}

		$this->_loan_rate_type = $loan_rate_type;
	}

	// function to set the private interest rate
	public function setIneterestRate($interest_rate) {
		// if passed in email is not between 1 and 255 characters
		if(strlen($interest_rate) > 255) {

			throw new LoanProductSettingsException("loan processing fees  error");
		}
		$this->_interest_rate = $interest_rate;
	}
	// function to set the private member address
	public function setMinmumAmount($minmum_amount) {
		// if passed in address is not between 1 and 255 characters
		if(strlen($minmum_amount) > 16777215) {
			throw new LoanProductSettingsException("minmum amount error");
		}
		$this->_minmum_amount = $minmum_amount;
	}
	// function to set the private member address
	public function setLoanProcessingFees($loan_processing_fees) {
		// if passed in address is not between 1 and 255 characters
		if(strlen($loan_processing_fees) > 16777215) {
			throw new LoanProductSettingsException("loan proessing fee error");
		}
		$this->_loan_processing_fees = $loan_processing_fees;
	}
	// function to set the private member address
	public function seMaximumAmount($maxmum_amount) {
		// if passed in address is not between 1 and 255 characters
		if(strlen($maxmum_amount) > 16777215) {
			throw new LoanProductSettingsException("maximum amount error");
		}
		$this->_maxmum_amount = $maxmum_amount;
	}

	// function to set the private member doj
	public function setNumberOfGuarantors($number_of_guarantors) {
		// if passed in doj is not between 1 and 255 characters
		if(strlen($number_of_guarantors) < 1 || strlen($number_of_guarantors) > 255) {
			throw new LoanProductSettingsException("Number of guarantors error");
		}
		$this->_number_of_guarantors = $number_of_guarantors;
	}

	// function to set the private member balance
	public function setCanClientBeSelfGuarantor($can_client_be_self_guarantor) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($can_client_be_self_guarantor) < 0 || strlen($can_client_be_self_guarantor) > 255) {
			throw new LoanProductSettingsException("can be self guarantor error");
		}
		$this->_can_client_be_self_guarantor = $can_client_be_self_guarantor;
	}
	// function to set the private member balance
	public function setDeductInstallmentBeforeDesbursment($deduct_installment_beofore_disbursment) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($deduct_installment_beofore_disbursment) < 0 || strlen($deduct_installment_beofore_disbursment) > 255) {
			throw new LoanProductSettingsException("should deduct installment before desbursment error");
		}
		$this->_deduct_installment_beofore_disbursment = $deduct_installment_beofore_disbursment;
	}
	// function to set the private member balance
	public function setDoesInterestChangeDefaulted($does_interest_change_defaulted) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($does_interest_change_defaulted) < 0 || strlen($does_interest_change_defaulted) > 255) {
			throw new LoanProductSettingsException("Member cbalance error");
		}
		$this->_does_interest_change_defaulted = $does_interest_change_defaulted;
	}
	// function to set the private member balance
	public function setNewInternetRate($new_interest_rate) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($new_interest_rate) < 0 || strlen($new_interest_rate) > 255) {
			throw new LoanProductSettingsException("New interest rate error");
		}
		$this->_new_interest_rate = $new_interest_rate;
	}

	public function setMustHaveSecurity($must_have_security) {
		// if passed in balance is not between 1 and 255 characters
		if(strlen($must_have_security) < 0 || strlen($must_have_security) > 255) {
			throw new LoanProductSettingsException("must have security error");
		}
		$this->_must_have_security = $must_have_security;
	}
	// function to set the private member status
	public function setChargePenalt($charge_penalt) {
		// if passed in status is not active or inactive
		if(!empty($charge_penalt) ? (strtolower($charge_penalt) !== 'yes' && strtolower($charge_penalt) !== 'no') : null) {
			throw new LoanProductSettingsException("charge penalty is not set to yes or no");
		}
		$this->_charge_penalt = strtolower($charge_penalt);
	}
  // function to return member object as an array for json
	public function returnLoanProductAsArray() {
		$loan_product = array();
		$loan_product['id'] = $this->getID();
		$loan_product['productname'] = $this->getProductName();
		$loan_product['loan_type'] = $this->getLoanType();
		$loan_product['installemt_payment'] = $this->getInstallPayment();
		$loan_product['loan_rate_type'] = $this->getLoanRateType();
		$loan_product['interest_rate'] = $this->getInterestRate();
		$loan_product['loan_processing_fee'] = $this->getLoanProcessingFees();
		$loan_product['min_loan_amt'] = $this->getMinimumAmount();
		$loan_product['max_loan_amt'] = $this->getMaxmumAmount();
		$loan_product['number_of_guarantors'] = $this->getNumberOfGuarantors();
		$loan_product['selfguarantor'] = $this->getClientBeSelfGuarantor();
		$loan_product['deductinstallbeforedisbursment'] = $this->getDeductInstallmentBeforeDesbursment();
		$loan_product['doesinterestchangedefaulted'] = $this->getDoesInterestChangeDefault();
		$loan_product['new_interest'] = $this->getNewInterestRate();
		$loan_product['must_have_security'] = $this->getMustHaveSecurity();
		$loan_product['charge_penalty'] = $this->getChargePenalt();
		return $loan_product;
	}

}
