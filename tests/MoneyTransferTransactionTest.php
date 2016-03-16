<?php
namespace Devture\ZenginGenerator;

class MoneyTransferTransactionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var MoneyTransferTransaction
	 */
	private $transaction;

	public function setUp() {
		parent::setUp();

		$bankAccount = new BankAccount();
		$bankAccount->setType(BankAccount::TYPE_NORMAL);
		$bankAccount->setBankName('ミツイスミトモ');
		$bankAccount->setBankCode('0009');
		$bankAccount->setBranchName('アカサカ');
		$bankAccount->setBranchCode('825');
		$bankAccount->setHolderName('スラビ');
		$bankAccount->setNumber('9876543');
		$bankAccount->setCompanyCode('1234560789');

		$this->transaction = new MoneyTransferTransaction();
		$this->transaction->setDestinationBankAccount($bankAccount);
		$this->transaction->setAmount(15234);
	}

	public function testValidationPasses() {
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 0);
	}

	public function testValidationFailsWithMissingBankAccount() {
		$this->transaction->setDestinationBankAccount(null);
		$this->assertSame(null, $this->transaction->getDestinationBankAccount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('destinationBankAccount')) === 1);
	}

	public function testValidationFailsWithBadAmounts() {
		$this->transaction->setAmount(null);
		$this->assertSame(null, $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);

		$this->transaction->setAmount('');
		$this->assertSame('', $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);

		$this->transaction->setAmount('500円');
		$this->assertSame('500円', $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);

		$this->transaction->setAmount(0);
		$this->assertSame(0, $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);

		$this->transaction->setAmount(-5);
		$this->assertSame(-5, $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);

		$this->transaction->setAmount(5.5);
		$this->assertSame(5.5, $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);

		//10 digits is ok.
		$this->transaction->setAmount(1234567890);
		$this->assertSame(1234567890, $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 0);

		//11 digits will cause an overflow.
		$this->transaction->setAmount(12345678900);
		$this->assertSame(12345678900, $this->transaction->getAmount());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('amount')) === 1);
	}

	public function testValidationFailsWithBadMemberCodes() {
		$this->transaction->setMemberCode(12345);
		$this->assertSame(12345, $this->transaction->getMemberCode());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 0);

		//10 digits is ok.
		$this->transaction->setMemberCode(1234567890);
		$this->assertSame(1234567890, $this->transaction->getMemberCode());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 0);

		//11 digits is too many.
		$this->transaction->setMemberCode(12345678900);
		$this->assertSame(12345678900, $this->transaction->getMemberCode());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('memberCode')) === 1);
	}

	public function testValidationFailsWithBadAffiliationCodes() {
		$this->transaction->setAffiliationCode(12345);
		$this->assertSame(12345, $this->transaction->getAffiliationCode());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 0);

		//10 digits is ok.
		$this->transaction->setAffiliationCode(1234567890);
		$this->assertSame(1234567890, $this->transaction->getAffiliationCode());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 0);

		//11 digits is too many.
		$this->transaction->setAffiliationCode(12345678900);
		$this->assertSame(12345678900, $this->transaction->getAffiliationCode());
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('affiliationCode')) === 1);
	}

	public function testValidationFailsWithCorruptedBankAccount() {
		//Ensuring that validation cascades and checks the bank account.
		$bankAccount = $this->transaction->getDestinationBankAccount();
		$bankAccount->setNumber(null);
		$violations = Validator::validateMoneyTransferTransaction($this->transaction);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('destinationBankAccount')) === 1);
	}

}
