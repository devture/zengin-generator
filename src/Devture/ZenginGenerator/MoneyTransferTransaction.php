<?php
namespace Devture\ZenginGenerator;

class MoneyTransferTransaction {

        private $amount;

	private $destinationBankAccount;

        private $memberCode;

        private $affiliationCode;

        public function setAmount($value) {
		$this->amount = $value;
	}

	public function getAmount() {
		return $this->amount;
	}

	public function setDestinationBankAccount(BankAccount $account = null) {
		$this->destinationBankAccount = $account;
	}

	public function getDestinationBankAccount() {
		return $this->destinationBankAccount;
	}

        public function setMemberCode($value) {
		$this->memberCode = $value;
	}

	public function getMemberCode() {
		return $this->memberCode;
	}

        public function setAffiliationCode($value) {
		$this->affiliationCode = $value;
	}

	public function getAffiliationCode() {
		return $this->affiliationCode;
	}

}
