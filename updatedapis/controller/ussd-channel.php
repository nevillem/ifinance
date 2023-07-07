<?php
require_once('../core/classes/USSD.php');
require_once('../core/initialize.php');
require_once '../core/credentials.php';
require('YoAPI.php');

$ussd = new USSD();
$yoAPI = new YoAPI($username, $password);

// get session varaibles
$session_transaction_id = $_REQUEST['transactionId'];
$msisdn = $_REQUEST['msisdn'];
$ussd_service_code = isset($_REQUEST['ussdServiceCode']) ? $_REQUEST['ussdServiceCode'] : null;
$user_input = $_REQUEST['ussdRequestString'];
$transaction_time = $_REQUEST['transactionTime'];

// Store the request in the database
$register_request = $ussd->register_ussd_request($session_transaction_id, $ussd_service_code, $msisdn, $user_input);
if (!$register_request['success']) {
	// $ussd->sendResponse($ussd->invalidResponse());
	$errorResponseOutput = $ussd->invalidResponse();
	$ussd->sendResponse($errorResponseOutput);
}

// check USSD session
$checkSession = $ussd->check_session($session_transaction_id, $msisdn);
if (!$checkSession['success']) {
	// $ussd->sendResponse($ussd->invalidResponse());
	$errorResponseOutput = $ussd->invalidResponse();
	$ussd->sendResponse($errorResponseOutput);
} else {
	if ($checkSession['count'] === 0) {
		// register session
		$register_session = $ussd->register_ussd_session($session_transaction_id, $msisdn);
		if ($register_session['success']) {
			$mainMenu = $ussd->get_main_menu();
			$ussd->sendResponse($mainMenu);
		} else {
			$errorResponseOutput = $ussd->invalidResponse();
			$ussd->sendResponse($errorResponseOutput);
		}

	} else {
		// get session data
		$getSessionData = $ussd->get_session_data($session_transaction_id, $msisdn);
		if (!$getSessionData['success'] || $getSessionData['count'] === 0) {
			$errorResponseOutput = $ussd->invalidResponse();
			$ussd->sendResponse($errorResponseOutput);
		}

		$sessionData = $getSessionData['data'];
		$currentLevel = (int)$sessionData['current_level'];

		// if user wants to go back to the main menu
		if ($user_input === '00') {
			// update session
			$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 0, 0, '');
			if (!$updateSession['success']) {
				$errorResponseOutput = $ussd->invalidResponse();
				$ussd->sendResponse($errorResponseOutput);
			}

			$mainMenu = $ussd->get_main_menu();
			$ussd->sendResponse($mainMenu);
		}

		// level 1
		if ($currentLevel === 0) {
			switch ($user_input) {

				case 1:
					// update session
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 1, 0, 'account_saving_menu', $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}

					$accountSavingMenu = $ussd->get_account_saving_menu();
					$ussd->sendResponse($accountSavingMenu);
					break;

				case 2:
					// update session
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 1, 0, 'account_withdraw_menu', $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}


					$accountWithdrawMenu = $ussd->get_account_withdraw_menu();
					$ussd->sendResponse($accountWithdrawMenu);
					break;

				case 3:
					// update session
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 1, 0, 'account_balance_menu', $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}


					$accountBalanceMenu = $ussd->get_account_balance_menu();
					$ussd->sendResponse($accountBalanceMenu);
					break;

					case 4:
					// update session
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 1, 0, 'account_share_menu');
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}


					$accountShareBalanceMenu = $ussd->get_account_share_balance_menu();
					$ussd->sendResponse($accountShareBalanceMenu);
					break;

                            case 5:
                                          // update session
                                          $updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 1, 0, 'account_loan_menu');
                                          if (!$updateSession['success']) {
                                                 $errorResponseOutput = $ussd->invalidResponse();
                                                 $ussd->sendResponse($errorResponseOutput);
                                          }


                                          $accountLoanBalanceMenu = $ussd->get_account_loan_balance_menu();
                                          $ussd->sendResponse($accountLoanBalanceMenu);
                                          break;

                            case 6:
                                                 // update session
                                                 $updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 1, 0, 'account_pin_menu');
                                                 if (!$updateSession['success']) {
                                                        $errorResponseOutput = $ussd->invalidResponse();
                                                        $ussd->sendResponse($errorResponseOutput);
                                                 }


                                                 $accountPinMenu = $ussd->get_account_pin_menu();
                                                 $ussd->sendResponse($accountPinMenu);
                                                 break;
				default:
					$errorResponseOutput = $ussd->invalidResponse();
					$ussd->sendResponse($errorResponseOutput);
					break;
			}

		}

		// level 2
		if ($currentLevel === 1) {
			// get next step
			$nextStep = $sessionData['next_step'];

			if ($user_input === '0') {
				$previousStep = $ussd->get_main_menu();
				$ussd->sendResponse($previousStep);
			}

			switch ($nextStep) {
				case 'account_saving_menu':

				// update session
				$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 2, 1, 'amount_summary_menu', 'amount_saving_menu', $user_input);
				if (!$updateSession['success']) {
					$errorResponseOutput = $ussd->invalidResponse();
					$ussd->sendResponse($errorResponseOutput);
				}
				$accountSavingAmountMenu = $ussd->account_saving_amount_menu();
				$ussd->sendResponse($accountSavingAmountMenu);

				break;

				case 'account_withdraw_menu':
				// update session
				$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 2, 1, 'amount_withdraw_pin_menu', 'amount_withdraw_menu', $user_input);
				if (!$updateSession['success']) {
					$errorResponseOutput = $ussd->invalidResponse();
					$ussd->sendResponse($errorResponseOutput);
				}

				$accountWithdrawAmountMenu = $ussd->account_withdraw_amount_menu();
				$ussd->sendResponse($accountWithdrawAmountMenu);

					break;

				case 'account_balance_menu':
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 2, 1, 'get_amount_balance_menu', 'pin_balance_menu', $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}

					$accountPinEntryMenu = $ussd->account_view_balance_pin_menu();
					$ussd->sendResponse($accountPinEntryMenu);

						break;

				case 'account_share_menu':
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 2, 1, 'get_share_balance_menu', 'pin_balance_menu', $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}

					$accountPinEntryMenu = $ussd->account_view_balance_pin_menu();
					$ussd->sendResponse($accountPinEntryMenu);

					break;
					case 'account_loan_menu':
						$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 2, 1, 'get_loan_balance_menu', 'pin_balance_menu', $user_input);
						if (!$updateSession['success']) {
							$errorResponseOutput = $ussd->invalidResponse();
							$ussd->sendResponse($errorResponseOutput);
						}

						$accountPinEntryMenu = $ussd->account_view_balance_pin_menu();
						$ussd->sendResponse($accountPinEntryMenu);

						break;
						case 'account_pin_menu':
							$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 2, 1, 'get_pin_menu_new', 'pin_balance_menu', $user_input);
							if (!$updateSession['success']) {
								$errorResponseOutput = $ussd->invalidResponse();
								$ussd->sendResponse($errorResponseOutput);
							}

							$accountPinEntryMenu = $ussd->account_view_balance_pin_menu();
							$ussd->sendResponse($accountPinEntryMenu);

							break;
					default:
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
						break;
				}
		}

		// level 3
		if ($currentLevel === 2) {
			// get next step
			$nextStep = $sessionData['next_step'];

			switch ($nextStep) {
				case 'amount_summary_menu':

					// previous step
					if ($user_input === '00') {
						$previousStep = $ussd->get_main_menu();
						$ussd->sendResponse($previousStep);
					}

					$accountData = $ussd->get_account_information($sessionData['code']);
						if (!$accountData['success'] || $accountData['count'] !== 1) {
							$errorResponseOutput = $ussd->invalidResponse('invalid account number. please try again.' .$account);
							$ussd->sendResponse($errorResponseOutput);
						}


					// update session
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 3, 2, 'confirm_account', 'account_information', $sessionData['code'], $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}

					$paymentDataInfo = $accountData['data']['member_fname'].' '.$accountData['data']['member_lname'];
					$paymentSaccoData = $accountData['data']['sacco_name'];
					$DepositConfirmMenu = $ussd->deposit_confirmation_menu($user_input, $sessionData['code'], $paymentDataInfo, $paymentSaccoData);
					$ussd->sendResponse($DepositConfirmMenu);
					break;

				case 'get_amount_balance_menu':

					// previous step
					if ($user_input === '00') {
						$previousStep = $ussd->get_main_menu();
						$ussd->sendResponse($previousStep);
					}

					// check pincode whether is valid to that specific account
					$checkAccountCredentials = $ussd->get_account_information($sessionData['code']);
					if (!$checkAccountCredentials['success'] || $checkAccountCredentials['count'] !== 1) {
						$errorResponseOutput = $ussd->invalidResponse('Invalid account. Please try again.');
						$ussd->sendResponse($errorResponseOutput);
					}
					if ($checkAccountCredentials['data']['member_pin'] !== md5($user_input)){
						$errorResponseOutput = $ussd->invalidResponse('Invalid account or pincode. Please try again.');
						$ussd->sendResponse($errorResponseOutput);
					}

					// update session
					$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 3, 2, 'DisplayInfo', 'Display Info', $sessionData['code'], $user_input);
					if (!$updateSession['success']) {
						$errorResponseOutput = $ussd->invalidResponse();
						$ussd->sendResponse($errorResponseOutput);
					}

					// get menu to display the acccount balance
					$type = "Savings";
					$balanceAmountMenu = $ussd->displayAccountBalances($type, $checkAccountCredentials['data']['members_account_volunteer'] ,$checkAccountCredentials['data']['sacco_name']);
					$ussd->sendResponse($balanceAmountMenu);
					break;

					case 'get_share_balance_menu':

						// previous step
						if ($user_input === '00') {
							$previousStep = $ussd->get_main_menu();
							$ussd->sendResponse($previousStep);
						}

						// check pincode whether is valid to that specific account
						$checkAccountCredentials = $ussd->get_account_information($sessionData['code']);
						if (!$checkAccountCredentials['success'] || $checkAccountCredentials['count'] !== 1) {
							$errorResponseOutput = $ussd->invalidResponse('Invalid account. Please try again.');
							$ussd->sendResponse($errorResponseOutput);
						}
						if ($checkAccountCredentials['data']['member_pin'] !== md5($user_input)){
							$errorResponseOutput = $ussd->invalidResponse('Invalid account or pincode. Please try again.');
							$ussd->sendResponse($errorResponseOutput);
						}

						// update session
						$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 3, 2, 'DisplayInfo', 'Display Info', $sessionData['code'], $user_input);
						if (!$updateSession['success']) {
							$errorResponseOutput = $ussd->invalidResponse();
							$ussd->sendResponse($errorResponseOutput);
						}

						// get menu to display the acccount balance
						$type = "shares";
						$balanceAmountMenu = $ussd->displayAccountBalances($type, $checkAccountCredentials['data']['member_account_shares'] ,$checkAccountCredentials['data']['sacco_name']);
						$ussd->sendResponse($balanceAmountMenu);
						break;

						case 'get_loan_balance_menu':

							// previous step
							if ($user_input === '00') {
								$previousStep = $ussd->get_main_menu();
								$ussd->sendResponse($previousStep);
							}

							// check pincode whether is valid to that specific account
							$checkAccountCredentials = $ussd->get_account_information($sessionData['code']);
							if (!$checkAccountCredentials['success'] || $checkAccountCredentials['count'] !== 1) {
								$errorResponseOutput = $ussd->invalidResponse('Invalid account. Please try again.');
								$ussd->sendResponse($errorResponseOutput);
							}
							if ($checkAccountCredentials['data']['member_pin'] !== md5($user_input)){
								$errorResponseOutput = $ussd->invalidResponse('Invalid account or pincode. Please try again.');
								$ussd->sendResponse($errorResponseOutput);
							}

							// update session
							$updateSession = $ussd->updateSession($session_transaction_id, $msisdn, 3, 2, 'DisplayInfo', 'Display Info', $sessionData['code'], $user_input);
							if (!$updateSession['success']) {
								$errorResponseOutput = $ussd->invalidResponse();
								$ussd->sendResponse($errorResponseOutput);
							}

							// get menu to display the acccount balance
							$type = "Loans";
							$balanceAmountMenu = $ussd->displayAccountBalances($type, $checkAccountCredentials['data']['members_account_compuslaory'] ,$checkAccountCredentials['data']['sacco_name']);
							$ussd->sendResponse($balanceAmountMenu);
							break;

					case 'get_pin_menu_new':

						break;

				default:
					$errorResponseOutput = $ussd->invalidResponse();
					$ussd->sendResponse($errorResponseOutput);
					break;
			}
		}

		// level 4
		if ($currentLevel === 3) {
			// get next step
			$nextStep = $sessionData['next_step'];

			switch ($nextStep) {
				case 'confirm_account':
					switch($user_input){
						case 1:
					$account = $sessionData['code'];
					$amount = $sessionData['amount'];

					if ($amount > 0 && $amount <= 2500):
						$charge = 115;
					    $amount = $amount + $charge;
					    elseif ($amount >= 2501 && $amount <= 5000):
									$charge = 145;
						 $amount = $amount + $charge;
						 elseif($amount >= 5001 && $amount <= 15000):
											$charge = 525;
						     $amount = $amount + $charge;
						     elseif($amount >= 15001 && $amount <= 30000):
													$charge = 575;
							  $amount = $amount + $charge;
							  elseif($amount >= 30001 && $amount <= 45000):
															$charge = 625;
							  $amount = $amount + $charge;
							  elseif($amount >= 45001 && $amount <= 60000):
															$charge = 700;
							  $amount = $amount + $charge;
							   elseif($amount >= 60001 && $amount <= 125000):
											 $charge = 805;
											 $amount = $amount + $charge;
                                elseif($amount >= 125001 && $amount <= 250000):
														$charge = 1000;
														$amount = $amount + $charge;
							    	 elseif($amount >= 250001 && $amount <= 500000):
															 $charge = 1300;
															 $amount = $amount + $charge;
										elseif($amount >= 500001 && $amount <= 1000000):
															 $charge = 3350;
															 $amount = $amount + $charge;
											elseif($amount >= 1000001 && $amount <= 2000000):
															 $charge = 5750;
															 $amount = $amount + $charge;
												elseif($amount >= 2000001 && $amount <= 4000000):
															 $charge = 5750;
															 $amount = $amount + $charge;
												    elseif($amount >= 4000001 && $amount <= 5000000):
															 $charge = 5750;
															 $amount = $amount + $charge;
													    elseif($amount > 5000000):
														$charge = 5750;
														$amount = $amount + $charge;
							  endif;
							  $paymentData = $ussd->get_account_information($account);
							if (!$paymentData['success'] || $paymentData['count'] !== 1) {
								$errorResponseOutput = $ussd->invalidResponse();
								$ussd->sendResponse($errorResponseOutput);
							}
								//transaction reference
								$transactionID = getGUIDnoHash();
								// generate external reference
								$transaction_reference = date("YmdHis").rand(1,100);
								$yoAPI->set_external_reference($transaction_reference);
								// $narrative = 'USSD payment';
								// register transaction
								$registerTransaction = $ussd->NewTransaction($paymentData['data']['member_id'], $transaction_reference, $msisdn, $amount, $paymentData['data']['saccos_sacco_id'], null , $charge ,$transactionID);
								if (!$registerTransaction['success']) {
									$errorResponseOutput = $ussd->invalidResponse();
									$ussd->sendResponse($errorResponseOutput);
								}

								// Set nonblocking to TRUE so that you get an instant response
								$yoAPI->set_nonblocking("TRUE");

								// Set an instant notification url where a successful payment notification POST will be sent
								// See documentation on how to handle IPN
								$yoAPI->set_instant_notification_url('https://api.test.irembofinance.com/ipn');

								// Set a failure notification url where a failed payment notification POST will be sent
								// See documentation on how to handle IPNs
								$yoAPI->set_failure_notification_url('https://api.test.irembofinance.com/fpn');

							$notificationResponse = $yoAPI->ac_deposit_funds($msisdn, $amount, 'USSD transaction');

	            						if ($notificationResponse['Status']=='OK') {
								// get confirmed payment menu
								$confirmedPaymentMenu = $ussd->confirmed_payment_menu();
								$ussd->sendResponse($confirmedPaymentMenu);
									} else if($notificationResponse['Status']=='ERROR'){
										$errorResponseOutput = $ussd->invalidResponse();
										$ussd->sendResponse($errorResponseOutput);
									}

					break;
					case 2:
						$errorResponseOutput = $ussd->invalidResponse('payment cancelled');
							$ussd->sendResponse($errorResponseOutput);
							break;
					default:
					$errorResponseOutput = $ussd->invalidResponse();
					$ussd->sendResponse($errorResponseOutput);
					break;

					}
					break;

				default:
					$errorResponseOutput = $ussd->invalidResponse();
					$ussd->sendResponse($errorResponseOutput);
					break;
			}
		}
	}
}
