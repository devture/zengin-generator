<?php
namespace Devture\ZenginGenerator;

use Devture\Component\Form\Validator\ViolationsList;

class Validator {

        const BANK_ACCOUNT_ROLE_SENDER = 'sender';
        const BANK_ACCOUNT_ROLE_RECEIVER = 'receiver';

        static public function validateBankAccount(BankAccount $entity, $role) {
                $violations = new ViolationsList();

                if (!in_array($entity->getType(), BankAccount::getKnownTypes())) {
                        $violations->add('type', 'Invalid bank account type: `%type%`.', array(
                                '%type%' => $entity->getType(),
                        ));
                }

                if (!$entity->getBankName()) {
			$violations->add('bankName', 'Bank name cannot be blank.');
		} else {
                        $length = StringUtil::strlen($entity->getBankName());
                        if ($length === 0 || $length > 15) {
        			$violations->add('bankName', 'Invalid bank name `%name%` (longer than %characters%).', array(
                                        '%name%' => $entity->getBankName(),
                                        '%characters%' => 15,
                                ));
                        } else if (!StringUtil::isStringKatakanaAndAlphanumeric($entity->getBankName())){
        			$violations->add('bankName', 'Invalid bank name `%name%` (contains bad characters).', array(
                                        '%name%' => $entity->getBankName(),
                                ));
                        }
                }

		if (!$entity->getBankCode()) {
			$violations->add('bankCode', 'Bank code cannot be blank.');
		} else if (!preg_match('/^\d{4}$/', $entity->getBankCode())) {
			$violations->add('bankCode', 'Invalid bank code `%code%` (needs to be 4 digits).', array(
                                '%code%' => $entity->getBankCode(),
                        ));
		}

		if (!$entity->getBranchName()) {
			$violations->add('branchName', 'Branch name cannot be blank.');
		} else {
                        $length = StringUtil::strlen($entity->getBranchName());
                        if ($length === 0 || $length > 15) {
        			$violations->add('branchName', 'Invalid branch name `%name%` (longer than %characters%).', array(
                                        '%name%' => $entity->getBranchName(),
                                        '%characters%' => 15,
                                ));
                        } else if (!StringUtil::isStringKatakanaAndAlphanumeric($entity->getBranchName())){
        			$violations->add('branchName', 'Invalid branch name `%name%` (contains bad characters).', array(
                                        '%name%' => $entity->getBranchName(),
                                ));
                        }
                }

        	if (!$entity->getBranchCode()) {
        		$violations->add('branchCode', 'Branch code cannot be blank.');
        	} else if (!preg_match('/^\d{3}$/', $entity->getBranchCode())) {
        		$violations->add('branchCode', 'Invalid branch code `%code%` (needs to be 3 digits).', array(
                                '%code%' => $entity->getBranchCode(),
                        ));
        	}

		if (!$entity->getNumber()) {
			$violations->add('number', 'Account number cannot be blank.');
		} else if (!preg_match('/^\d{7}$/', $entity->getNumber())) {
        		$violations->add('number', 'Invalid account number `%number%` (needs to be 7 digits).', array(
                                '%number%' => $entity->getNumber(),
                        ));
		}

                if (!$entity->getHolderName()) {
                        //Can be blank.
                } else {
                        $length = StringUtil::strlen($entity->getHolderName());

                        //40 characters is allowed for the sender. 30 for the receiver.
                        $maximumLength = ($role === self::BANK_ACCOUNT_ROLE_SENDER ? 40 : 30);

                        if ($length === 0 || $length > $maximumLength) {
        			$violations->add('holderName', 'Invalid holder name `%name%` (longer than %characters%).', array(
                                        '%name%' => $entity->getHolderName(),
                                        '%characters%' => $maximumLength,
                                ));
                        } else if (!StringUtil::isStringKatakanaAndAlphanumeric($entity->getHolderName())){
        			$violations->add('holderName', 'Invalid holder name `%name%` (contains bad characters).', array(
                                        '%name%' => $entity->getHolderName(),
                                ));
                        }
                }

		if (!$entity->getCompanyCode()) {
                        //Can be blank.
		} else if (!preg_match('/^\d{10}$/', $entity->getCompanyCode())) {
        		$violations->add('companyCode', 'Invalid company code `%code%` (needs to be 10 digits).', array(
                                '%code%' => $entity->getCompanyCode(),
                        ));
		}

                return $violations;
        }

        static public function validateMoneyTransferTransaction(MoneyTransferTransaction $entity) {
                $violations = new ViolationsList();

                if (!($entity->getDestinationBankAccount() instanceof BankAccount)) {
			$violations->add('destinationBankAccount', 'Destination bank account cannot be blank.');
                } else {
                        $violationsBankAccount = static::validateBankAccount($entity->getDestinationBankAccount(), self::BANK_ACCOUNT_ROLE_RECEIVER);
                        if (count($violationsBankAccount) > 0) {
                                $violations->add('destinationBankAccount', 'Destination bank account is invalid: ' . print_r($violationsBankAccount, true));
                        }
                }

        	if (!$entity->getAmount()) {
        		$violations->add('amount', 'Amount cannot be blank.');
        	} else if (!preg_match('/^\d{1,10}$/', $entity->getAmount())) {
        		$violations->add('amount', 'Invalid amount `%amount%` (needs to be a positive number and less than %digits% digits).', array(
                                '%amount%' => $entity->getAmount(),
                                '%digits%' => 10,
                        ));
        	}

        	if (!$entity->getMemberCode()) {
        		//Can be blank.
        	} else if (!preg_match('/^\d{1,10}$/', $entity->getMemberCode())) {
        		$violations->add('memberCode', 'Invalid member code `%code%` (needs to be a number and less than %digits% digits).', array(
                                '%code%' => $entity->getMemberCode(),
                                '%digits%' => 10,
                        ));
        	}

        	if (!$entity->getAffiliationCode()) {
        		//Can be blank.
        	} else if (!preg_match('/^\d{1,10}$/', $entity->getAffiliationCode())) {
        		$violations->add('affiliationCode', 'Invalid affiliation code `%code%` (needs to be a number and less than %digits% digits).', array(
                                '%code%' => $entity->getAffiliationCode(),
                                '%digits%' => 10,
                        ));
        	}

                return $violations;
        }

        static public function validateTransferRequest(TransferRequest $entity) {
                $violations = new ViolationsList();

                if (!in_array($entity->getType(), TransferRequest::getKnownTypes())) {
                        $violations->add('type', 'Invalid transfer request type: `%type%`.', array(
                                '%type%' => $entity->getType(),
                        ));
                }

                if (!($entity->getSourceBankAccount() instanceof BankAccount)) {
			$violations->add('sourceBankAccount', 'Source bank account cannot be blank.');
                } else {
                        $violationsBankAccount = static::validateBankAccount($entity->getSourceBankAccount(), self::BANK_ACCOUNT_ROLE_SENDER);
                        if (count($violationsBankAccount) > 0) {
                                $violations->add('sourceBankAccount', 'Source bank account is invalid.');
                        }
                }

                if (!($entity->getDate() instanceof \DateTime)) {
			$violations->add('date', 'Date cannot be blank.');
                }

                if (count($entity->getTransactions()) == 0) {
			$violations->add('transactions', 'A transfer request needs to contain at least one transaction.');
                }

                foreach ($entity->getTransactions() as $transaction) {
                        $violationsTransaction = static::validateMoneyTransferTransaction($transaction);
                        if (count($violationsTransaction) > 0) {
                                $violations->add('transactions', 'Transaction is invalid.');
                        }
                }

                return $violations;
        }

}
