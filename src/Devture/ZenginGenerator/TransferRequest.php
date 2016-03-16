<?php
namespace Devture\ZenginGenerator;

class TransferRequest {

	const TYPE_GENERAL = 'general';
	const TYPE_SALARY = 'salary';
	const TYPE_REWARD = 'reward';

        private $type;

	/**
	 * @var BankAccount|NULL
	 */
	private $sourceBankAccount;

	/**
	 * @var \DateTime|NULL
	 */
	private $date;

	/**
	 * @var MoneyTransferTransaction[]
	 */
	private $transactions = array();

        public function setType($value) {
		$this->type = $value;
	}

	public function getType() {
		return $this->type;
	}

	public function setSourceBankAccount(BankAccount $account = null) {
		$this->sourceBankAccount = $account;
	}

	public function getSourceBankAccount() {
		return $this->sourceBankAccount;
	}

	public function setDate(\DateTime $value = null) {
		$this->date = $value;
	}

	public function getDate() {
		return $this->date;
	}

	public function addTransaction(MoneyTransferTransaction $transaction) {
		$this->transactions[] = $transaction;
	}

	public function getTransactions() {
		return $this->transactions;
	}

	public function clearTransactions() {
		$this->transactions = array();
	}

	static public function getKnownTypes() {
		$r = new \ReflectionClass(__CLASS__);
		$results = array();
		foreach ($r->getConstants() as $name => $value) {
			if (strpos($name, 'TYPE_') === 0) {
				$results[] = $value;
			}
		}
		return $results;
	}

}
