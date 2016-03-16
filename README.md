# Zengin Generator

**FB (Firm-Banking) Zengin format (http://www.sekishinkin.co.jp/web_fb/zengin.html) file generator (Japanese bank-transfer file)**


## Preface

This library is still in early development.
**Do not use in production (yet).**

The purposes of "zengin" files is to facilitate mass bank-transfer operations on Japanese banks' Firm-Banking service.

The zengin file is a Shift-JIS encoded file, following a special format,
[somewhat described here](http://www.sekishinkin.co.jp/web_fb/zengin.html).

Input data to this library should use Katakana. If not, an exception will be thrown.
Restrictions apply to some fields (~15-30 characters) for bank/branch/people names.


## Installation

Using [Composer](http://getcomposer.org/)

    $ composer require devture/zengin-generator


## Usage

See the [tests/](tests/) directory, and more specifically, the [ZenginGeneratorTest.php file](tests/ZenginGeneratorTest.php).
