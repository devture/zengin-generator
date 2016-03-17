<?php
namespace Devture\ZenginGenerator;

class BankAccountValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var BankAccount
	 */
	private $account;

	public function setUp() {
		parent::setUp();

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

	public function testValidationPasses() {
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 0);
	}

	public function testValidationPassesEvenWithChangedType() {
		$this->account->setType(BankAccount::TYPE_CURRENT);
		$this->assertSame(BankAccount::TYPE_CURRENT, $this->account->getType());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 0);
	}

	public function testValidationFailsOnIncorrectType() {
		$this->account->setType('unknown');
		$this->assertSame('unknown', $this->account->getType());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('type')) === 1);
	}

	public function testValidationFailsOnEmptyBankName() {
		$this->account->setBankName('');
		$this->assertSame('', $this->account->getBankName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);

		$this->account->setBankName(null);
		$this->assertSame(null, $this->account->getBankName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);
	}

	public function testValidationFailsOnNonKatakanaBankName() {
		//Hiragana
		$this->account->setBankName('てすと');
		$this->assertSame('てすと', $this->account->getBankName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);

		//Half-width Katakana
		$this->account->setBankName('ﾐﾂｲｽﾐﾄﾓ');
		$this->assertSame('ﾐﾂｲｽﾐﾄﾓ', $this->account->getBankName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);

		//Kanji
		$this->account->setBankName('銀行');
		$this->assertSame('銀行', $this->account->getBankName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);

		//Cyrillic
		$this->account->setBankName('Банка');
		$this->assertSame('Банка', $this->account->getBankName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);
	}

	public function testValidationPassesOnEnglishOrKatakanaMixedBankName() {
		$this->account->setBankName('Test');
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 0);

		$this->account->setBankName('Test and カタカナ');
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 0);
	}

	public function testValidationFailsOnLongBankName() {
		$this->account->setBankName(str_repeat('T', 15));
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 0);

		$this->account->setBankName(str_repeat('T', 16));
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('bankName')) === 1);
	}

	public function testValidationFailsOnEmptyBranchName() {
	        $this->account->setBranchName('');
	        $this->assertSame('', $this->account->getBranchName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchName')) === 1);

	        $this->account->setBranchName(null);
	        $this->assertSame(null, $this->account->getBranchName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchName')) === 1);
	}

	public function testValidationFailsOnNonKatakanaBranchName() {
	        //Hiragana
	        $this->account->setBranchName('てすと');
	        $this->assertSame('てすと', $this->account->getBranchName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchName')) === 1);

		//Half-width Katakana
		$this->account->setBranchName('ﾐﾂｲｽﾐﾄﾓ');
		$this->assertSame('ﾐﾂｲｽﾐﾄﾓ', $this->account->getBranchName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('branchName')) === 1);

	        //Kanji
	        $this->account->setBranchName('銀行');
	        $this->assertSame('銀行', $this->account->getBranchName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchName')) === 1);

	        //Cyrillic
	        $this->account->setBranchName('Банка');
	        $this->assertSame('Банка', $this->account->getBranchName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchName')) === 1);
	}

	public function testValidationPassesOnEnglishOrKatakanaMixedBranchName() {
	        $this->account->setBranchName('Test');
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

	        $this->account->setBranchName('Test and カタカナ');
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);
	}

	public function testValidationFailsOnLongBranchNames() {
	        $this->account->setBranchName(str_repeat('T', 15));
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

	        $this->account->setBranchName(str_repeat('T', 16));
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchName')) === 1);
	}

	public function testValidationFailsOnNonKatakanaHolderName() {
	        //Hiragana
	        $this->account->setHolderName('すらび');
	        $this->assertSame('すらび', $this->account->getHolderName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);

		//Half-width Katakana
		$this->account->setHolderName('ﾐﾂｲｽﾐﾄﾓ');
		$this->assertSame('ﾐﾂｲｽﾐﾄﾓ', $this->account->getHolderName());
		$violations = Validator::validateBankAccount($this->account, null);
		$this->assertTrue(count($violations) === 1);
		$this->assertTrue(count($violations->get('holderName')) === 1);

	        //Kanji
	        $this->account->setHolderName('宮城');
	        $this->assertSame('宮城', $this->account->getHolderName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);

	        //Cyrillic
	        $this->account->setHolderName('Слави');
	        $this->assertSame('Слави', $this->account->getHolderName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);
	}

	public function testValidationPassesOnEnglishOrKatakanaMixedHolderName() {
	        $this->account->setHolderName('Slavi');
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

	        $this->account->setHolderName('Slavi and スラヴィ');
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);
	}

	public function testValidationFailsOnLongHolderNames() {
	        $this->account->setHolderName(str_repeat('T', 30));
	        $violations = Validator::validateBankAccount($this->account, Validator::BANK_ACCOUNT_ROLE_RECEIVER);
	        $this->assertTrue(count($violations) === 0);

	        $this->account->setHolderName(str_repeat('T', 31));
	        $violations = Validator::validateBankAccount($this->account, Validator::BANK_ACCOUNT_ROLE_RECEIVER);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);

	        $this->account->setHolderName(str_repeat('T', 40));
	        $violations = Validator::validateBankAccount($this->account, Validator::BANK_ACCOUNT_ROLE_SENDER);
	        $this->assertTrue(count($violations) === 0);

	        $this->account->setHolderName(str_repeat('T', 41));
	        $violations = Validator::validateBankAccount($this->account, Validator::BANK_ACCOUNT_ROLE_SENDER);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);
	}

	public function testValidationFailsOnEmptyHolderName() {
	        $this->account->setHolderName('');
		$this->assertSame('', $this->account->getHolderName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);

	        $this->account->setHolderName(null);
		$this->assertSame(null, $this->account->getHolderName());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('holderName')) === 1);
	}

	public function testValidationFailsOnNon4DigitBankCodes() {
		$this->account->setBankCode('12345');
		$this->assertSame('12345', $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('bankCode')) === 1);

		$this->account->setBankCode('1234 ');
		$this->assertSame('1234 ', $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('bankCode')) === 1);

		$this->account->setBankCode('123');
		$this->assertSame('123', $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('bankCode')) === 1);

		$this->account->setBankCode('１２３４');
		$this->assertSame('１２３４', $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('bankCode')) === 1);

		$this->account->setBankCode('1234');
		$this->assertSame('1234', $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

		$this->account->setBankCode('');
		$this->assertSame('', $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('bankCode')) === 1);

		$this->account->setBankCode(null);
		$this->assertSame(null, $this->account->getBankCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('bankCode')) === 1);
	}

	public function testValidationFailsOnNon3DigitBranchCodes() {
		$this->account->setBranchCode('1234');
		$this->assertSame('1234', $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchCode')) === 1);

		$this->account->setBranchCode('123 ');
		$this->assertSame('123 ', $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchCode')) === 1);

		$this->account->setBranchCode('12');
		$this->assertSame('12', $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchCode')) === 1);

		$this->account->setBranchCode('１２３');
		$this->assertSame('１２３', $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchCode')) === 1);

		$this->account->setBranchCode('123');
		$this->assertSame('123', $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

		$this->account->setBranchCode('');
		$this->assertSame('', $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchCode')) === 1);

		$this->account->setBranchCode(null);
		$this->assertSame(null, $this->account->getBranchCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('branchCode')) === 1);
	}

	public function testValidationFailsOnNon7DigitAccountNumbers() {
		$this->account->setNumber('12345678');
		$this->assertSame('12345678', $this->account->getNumber());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('number')) === 1);

		$this->account->setNumber('123456');
		$this->assertSame('123456', $this->account->getNumber());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('number')) === 1);

		$this->account->setNumber('１２３４５６７');
		$this->assertSame('１２３４５６７', $this->account->getNumber());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('number')) === 1);

		$this->account->setNumber('1234567');
		$this->assertSame('1234567', $this->account->getNumber());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

		$this->account->setNumber('');
		$this->assertSame('', $this->account->getNumber());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('number')) === 1);

		$this->account->setNumber(null);
		$this->assertSame(null, $this->account->getNumber());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('number')) === 1);
	}

	public function testValidationFailsOnNon10DigitCompanyCodes() {
		$this->account->setCompanyCode('01234567890');
		$this->assertSame('01234567890', $this->account->getCompanyCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('companyCode')) === 1);

		$this->account->setCompanyCode('012345678');
		$this->assertSame('012345678', $this->account->getCompanyCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('companyCode')) === 1);

		$this->account->setCompanyCode('０１２３４５６７８９');
		$this->assertSame('０１２３４５６７８９', $this->account->getCompanyCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 1);
	        $this->assertTrue(count($violations->get('companyCode')) === 1);

		$this->account->setCompanyCode('0123456789');
		$this->assertSame('0123456789', $this->account->getCompanyCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

		//Not required.
		$this->account->setCompanyCode('');
		$this->assertSame('', $this->account->getCompanyCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);

		//Not required.
		$this->account->setCompanyCode(null);
		$this->assertSame(null, $this->account->getCompanyCode());
	        $violations = Validator::validateBankAccount($this->account, null);
	        $this->assertTrue(count($violations) === 0);
	}

}
