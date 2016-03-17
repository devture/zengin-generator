<?php
namespace Devture\ZenginGenerator;

class ZenginGeneratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ZenginGenerator
	 */
	private $generator;

	/**
	 * @var BankAccount
	 */
	private $account;

	public function setUp() {
		parent::setUp();

		$this->generator = new ZenginGenerator();

		$this->account = new BankAccount();
		$this->account->setType(BankAccount::TYPE_NORMAL);
		$this->account->setBankName('ミツイスミトモ');
		$this->account->setBankCode('0009');
		$this->account->setBranchName('アカサカ');
		$this->account->setBranchCode('825');
		$this->account->setHolderName('スラビ');
		$this->account->setNumber('9876543');
		$this->account->setCompanyCode('1234560789');
	}

	public function testEmptyFileGeneration() {
		$transferRequest = new TransferRequest();
		$transferRequest->setType(TransferRequest::TYPE_GENERAL);
		$transferRequest->setSourceBankAccount($this->account);
		$transferRequest->setDate(\DateTime::createFromFormat('Y-m-d', '2016-02-25'));

		$zenginContentShiftJis = $this->generator->generateNoValidation($transferRequest);
		$this->validateZenginContent($zenginContentShiftJis, 'results/empty.txt');

		try {
			$this->generator->generate($transferRequest);
			$this->fail('Expected exception, but succeeded.');
		} catch (\Devture\ZenginGenerator\Exception\PrecheckGenerationException $e) {
			$this->assertInstanceOf('\\Devture\\Component\\Form\\Validator\\ViolationsList', $e->getViolations());
		}
	}

	public function testSingleTransaction() {
		$transferRequest = new TransferRequest();
		$transferRequest->setType(TransferRequest::TYPE_GENERAL);
		$transferRequest->setSourceBankAccount($this->account);
		$transferRequest->setDate(\DateTime::createFromFormat('Y-m-d', '2016-02-25'));

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
		$transferRequest->addTransaction($receiverTransaction);

		$zenginContentShiftJis = $this->generator->generate($transferRequest);

		//Let's verify that カブシキガイシャ's "small" (full-width) ャ is converted to a big ヤ (half-width).
		//We do compare to the result file below, but we'd like to explicitly check for this beforehand anyway.
		$zenginContentUtf = \mb_convert_encoding($zenginContentShiftJis, 'utf-8', 'cp932');
		$this->assertTrue(str_replace('ﾃｽﾄｰ.ｶﾌﾞｼｷｶﾞｲｼﾔ', '', $zenginContentUtf) !== $zenginContentUtf);

		$this->assertTrue(str_replace(1103661, '', $zenginContentUtf) !== $zenginContentUtf, "Cannot find total amount.");

		$this->validateZenginContent($zenginContentShiftJis, 'results/single-transaction.txt');
	}

	public function testMultiTransaction() {
		$transferRequest = new TransferRequest();
		$transferRequest->setType(TransferRequest::TYPE_GENERAL);
		$transferRequest->setSourceBankAccount($this->account);
		$transferRequest->setDate(\DateTime::createFromFormat('Y-m-d', '2016-03-12'));

		$receiverBankAccount = new BankAccount();
		$receiverBankAccount->setType(BankAccount::TYPE_CURRENT);
		$receiverBankAccount->setBankName('トウキョウトミン');
		$receiverBankAccount->setHolderName('テストー.カブシキガイシャ'); //Note: using a normal dot/dash in this example
		$receiverBankAccount->setBankCode('0137');
		$receiverBankAccount->setBranchName('シブヤ');
		$receiverBankAccount->setBranchCode('031');
		$receiverBankAccount->setNumber('1231990');
		$violations = Validator::validateBankAccount($receiverBankAccount, Validator::BANK_ACCOUNT_ROLE_RECEIVER);
		$this->assertTrue(count($violations) === 0);
		$receiverTransaction = new MoneyTransferTransaction();
		$receiverTransaction->setDestinationBankAccount($receiverBankAccount);
		$receiverTransaction->setAmount(1103661);
		$violations = Validator::validateMoneyTransferTransaction($receiverTransaction);
		$this->assertTrue(count($violations) === 0);
		$transferRequest->addTransaction($receiverTransaction);

		$receiverBankAccount = new BankAccount();
		$receiverBankAccount->setType(BankAccount::TYPE_NORMAL);
		$receiverBankAccount->setBankName('ミツビシトウキョウUFJ');
		$receiverBankAccount->setHolderName('カ)ゼンギン'); //Note: using a normal dot in this example
		$receiverBankAccount->setBankCode('0005');
		$receiverBankAccount->setBranchName('ギンザドオリ');
		$receiverBankAccount->setBranchCode('024');
		$receiverBankAccount->setNumber('2275535');
		$violations = Validator::validateBankAccount($receiverBankAccount, Validator::BANK_ACCOUNT_ROLE_RECEIVER);
		$this->assertTrue(count($violations) === 0);
		$receiverTransaction = new MoneyTransferTransaction();
		$receiverTransaction->setDestinationBankAccount($receiverBankAccount);
		$receiverTransaction->setAmount(599300);
		$violations = Validator::validateMoneyTransferTransaction($receiverTransaction);
		$this->assertTrue(count($violations) === 0);
		$transferRequest->addTransaction($receiverTransaction);

		$zenginContentShiftJis = $this->generator->generate($transferRequest);

		//Let's verify that ミツビシトウキョウUFJ "small" (full-width + latin + small voiced) is converted correctly.
		//We do compare to the result file below, but we'd like to explicitly check for this beforehand anyway.
		$zenginContentUtf = \mb_convert_encoding($zenginContentShiftJis, 'utf-8', 'cp932');
		$this->assertTrue(str_replace('ﾐﾂﾋﾞｼﾄｳｷﾖｳUFJ', '', $zenginContentUtf) !== $zenginContentUtf);

		$this->assertTrue(str_replace(1103661 + 599300, '', $zenginContentUtf) !== $zenginContentUtf, "Cannot find total amount.");

		$this->validateZenginContent($zenginContentShiftJis, 'results/multi-transaction.txt');
	}

	private function validateZenginContent($zenginContentShiftJis, $expectedSameAsFilePath) {
		if (strpos($zenginContentShiftJis, "\r\n") === false) {
			$this->fail("Cannot find any lines.");
		}

		//We do some generic checks first, to catch major discrepancies.
		//Comparing to the contents at $expectedSameAsFilePath certainly catches everything,
		//but in case we fail, we don't want to say "different", if we can provide a better error message.
		$lines = explode("\r\n", $zenginContentShiftJis);
		foreach ($lines as $line) {
			$this->assertSame(120, strlen($line), sprintf('Line: %s', \mb_convert_encoding($line, 'utf-8', 'cp932')));
		}

		$expectedContent = file_get_contents(__DIR__ . '/' . $expectedSameAsFilePath);
		$this->assertSame($zenginContentShiftJis, $expectedContent);
	}

}
