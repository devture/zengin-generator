<?php
namespace Devture\ZenginGenerator;

class TransferRequestTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TransferRequest
	 */
	private $transferRequest;

	public function setUp() {
		parent::setUp();

		$senderBankAccount = new BankAccount();
		$senderBankAccount->setType(BankAccount::TYPE_NORMAL);
		$senderBankAccount->setBankName('ミツイスミトモ');
		$senderBankAccount->setBankCode('0009');
		$senderBankAccount->setBranchName('アカサカ');
		$senderBankAccount->setBranchCode('825');
		$senderBankAccount->setHolderName('スラビ');
		$senderBankAccount->setNumber('9876543');
		$senderBankAccount->setCompanyCode('1234560789');

		$this->transferRequest = new TransferRequest();
		$this->transferRequest->setType(TransferRequest::TYPE_GENERAL);
		$this->transferRequest->setSourceBankAccount($senderBankAccount);
		$this->transferRequest->setDate(\DateTime::createFromFormat('Y-m-d', '2016-02-25'));

		$receiverBankAccount = new BankAccount();
		$receiverBankAccount->setType(BankAccount::TYPE_NORMAL);
		$receiverBankAccount->setBankName('トウキョウトミン');
		$receiverBankAccount->setHolderName('テストー.カブシキガイシャ'); //Note: using a normal dot/dash in this example
		$receiverBankAccount->setBankCode('0137');
		$receiverBankAccount->setBranchName('シブヤ');
		$receiverBankAccount->setBranchCode('031');
		$receiverBankAccount->setNumber('1231990');
		$receiverTransaction = new MoneyTransferTransaction();
		$receiverTransaction->setDestinationBankAccount($receiverBankAccount);
		$receiverTransaction->setAmount(1103661);
		$this->transferRequest->addTransaction($receiverTransaction);

		$receiverBankAccount = new BankAccount();
		$receiverBankAccount->setType(BankAccount::TYPE_NORMAL);
		$receiverBankAccount->setBankName('ベツバンク');
		$receiverBankAccount->setHolderName('ヒトノナマエ'); //Note: using a normal dot/dash in this example
		$receiverBankAccount->setBankCode('0132');
		$receiverBankAccount->setBranchName('イケブクロ');
		$receiverBankAccount->setBranchCode('021');
		$receiverBankAccount->setNumber('1331990');
		$receiverTransaction = new MoneyTransferTransaction();
		$receiverTransaction->setDestinationBankAccount($receiverBankAccount);
		$receiverTransaction->setAmount(657856);
		$this->transferRequest->addTransaction($receiverTransaction);
	}

	public function testValidationPasses() {
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 0);
	}

	public function testValidationPassesEvenWithChangedType() {
		$this->transferRequest->setType(TransferRequest::TYPE_REWARD);
		$this->assertSame(TransferRequest::TYPE_REWARD, $this->transferRequest->getType());
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 0);
	}

	public function testValidationFailsOnIncorrectType() {
		$this->transferRequest->setType('unknown');
		$this->assertSame('unknown', $this->transferRequest->getType());
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('type')) === 1);
	}

	public function testValidationFailsWithMissingSourceBankAccount() {
		$this->transferRequest->setSourceBankAccount(null);
		$this->assertSame(null, $this->transferRequest->getSourceBankAccount());
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('sourceBankAccount')) === 1);
	}

	public function testValidationFailsWithMissingDate() {
		$this->transferRequest->setDate(null);
		$this->assertSame(null, $this->transferRequest->getDate());
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('date')) === 1);
	}

	public function testValidationFailsWithNoTransactions() {
		$this->transferRequest->clearTransactions(array());
		$this->assertSame(0, count($this->transferRequest->getTransactions()));
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('transactions')) === 1);
	}

	public function testValidationFailsWithCorruptedTransaction() {
		//Ensuring that validation cascades and checks transactions.
		$transaction = $this->transferRequest->getTransactions()[0];
		$transaction->setAmount(-500);
		$violations = Validator::validateTransferRequest($this->transferRequest);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('transactions')) === 1);
	}

}
