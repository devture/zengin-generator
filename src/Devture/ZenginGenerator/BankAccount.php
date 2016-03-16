<?php
namespace Devture\ZenginGenerator;

class BankAccount {

	const TYPE_NORMAL = 'normal';
	const TYPE_CURRENT = 'current';

        private $type;

        private $bankName;

        //4-digit bank code
        private $bankCode;

        private $branchName;

        //3-digit branch code
        private $branchCode;

        //10-digit bank-issued company code
        private $companyCode;

        private $holderName;

        //7-digit account number
        private $number;

        public function setType($value) {
		$this->type = $value;
	}

	public function getType() {
		return $this->type;
	}

	public function setBankName($value) {
		$this->bankName = $value;
	}

	public function getBankName() {
		return $this->bankName;
	}

	public function setBranchName($value) {
		$this->branchName = $value;
	}

	public function getBankCode() {
		return $this->bankCode;
	}

	public function setBankCode($value) {
		$this->bankCode = $value;
	}

	public function getBranchName() {
		return $this->branchName;
	}

	public function setBranchCode($value) {
		$this->branchCode = $value;
	}

	public function getBranchCode() {
		return $this->branchCode;
	}

	public function setCompanyCode($value) {
		$this->companyCode = $value;
	}

	public function getCompanyCode() {
		return $this->companyCode;
	}

	public function setHolderName($value) {
		$this->holderName = $value;
	}

	public function getHolderName() {
		return $this->holderName;
	}

	public function setNumber($value) {
		$this->number = $value;
	}

	public function getNumber() {
		return $this->number;
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
