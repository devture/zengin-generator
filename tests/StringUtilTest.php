<?php
namespace Devture\ZenginGenerator;

class StringUtilTest extends \PHPUnit_Framework_TestCase {

	public function testSprintfItemGroupsToString() {
		$itemGroups = array(
			array('%05d', 34),
			array('%s', 'test'),
		);
		$this->assertSame('00034test', StringUtil::sprintfItemGroupsToString($itemGroups));

		$this->assertSame('', StringUtil::sprintfItemGroupsToString(array()));
	}

	public function testConvertFullWidthToHalfWidthKana() {
		//Normal full-width to half-width conversion.
		$this->assertSame('ﾐﾂｲｽﾐﾄﾓ', StringUtil::convertFullWidthToHalfWidthKana('ミツイスミトモ'));

		//Normal full-width + ASCII to half-width conversion.
		$this->assertSame('ﾐﾂｲｽﾐﾄﾓ.With.ASCII', StringUtil::convertFullWidthToHalfWidthKana('ミツイスミトモ.With.ASCII'));

		//Full-width with "small" voice characeters to all-uppercase half-width conversion.
		$this->assertSame('ｶﾌﾞｼｷｶﾞｲｼﾔ', StringUtil::convertFullWidthToHalfWidthKana('カブシキガイシャ'));

		//Hiragana, kanji and other unknown characters should be left untouched
		$this->assertSame('銀行のБанка', StringUtil::convertFullWidthToHalfWidthKana('銀行のБанка'));
	}

	public function testStringPadRight() {
		$this->assertSame('ミ  ', StringUtil::stringPadRight('ミ', 3, ' '));
		$this->assertSame('ミニ.', StringUtil::stringPadRight('ミニ', 3, '.'));

		$this->assertSame('ミニミ', StringUtil::stringPadRight('ミニミ', 3, '.'));

		$this->setExpectedException('\InvalidArgumentException');
		StringUtil::stringPadRight('ミニミ', 2, '.');
	}

	public function testIsStringKatakanaAndAlphanumeric() {
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('カタカナ'));
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('カタ.カナ'));
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('カタカナー'));
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('カタカナAndAscii'));
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('カタカナ And Ascii'));
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('カタカナ And Ascii And 123'));
		$this->assertTrue(StringUtil::isStringKatakanaAndAlphanumeric('abcd)?-')); //just some ascii characters

		$this->assertFalse(StringUtil::isStringKatakanaAndAlphanumeric('カタカナではない'));
		$this->assertFalse(StringUtil::isStringKatakanaAndAlphanumeric('漢字'));
		$this->assertFalse(StringUtil::isStringKatakanaAndAlphanumeric('１２３'));
		$this->assertFalse(StringUtil::isStringKatakanaAndAlphanumeric('カタカナ　And　English')); //Japanese space
		$this->assertFalse(StringUtil::isStringKatakanaAndAlphanumeric('ｶﾀｶﾅ')); //half-width
		$this->assertFalse(StringUtil::isStringKatakanaAndAlphanumeric('カタｶﾅ')); //mixed: full-width + half-width
	}

}
