<?php
namespace Devture\ZenginGenerator;

class ZenginGenerator {

        const RECORD_HEADER = 1;
        const RECORD_DATA = 2;
        const RECORD_TRAILER = 8;
        const RECORD_END = 9;

        const CHARSET_JIS = 0;
        const CHARSET_EBCDIC = 1;

        /**
         * Generates a zengin-formatted data file for the given transfer request
         *
         * Performs data validation before actual generation, to make sure the data
         * doesn't contain any obvious inconsistencies, which will surely make generation fail.
         *
         * @param TransferRequest $transferRequest
         * @throws \Devture\ZenginGenerator\Exception\PrecheckGenerationException - when pre-generation validation fails
         * @throws \Devture\ZenginGenerator\Exception\RuntimeGenerationException - when problems are encountered during generation
         * @return string - Shift-JIS encoded zengin-formatted file
         */
        public function generate(TransferRequest $request) {
                $violations = Validator::validateTransferRequest($request);
                if (count($violations) > 0) {
                        throw new \Devture\ZenginGenerator\Exception\PrecheckGenerationException('Pre-check failure', $violations);
                }
                return $this->generateNoValidation($request);
        }

        /**
         * Generates a zengin-formatted data file for the given transfer request without doing data-validation
         *
         * Exceptions may still occur during runtime.
         *
         * @param TransferRequest $transferRequest
         * @throws \Devture\ZenginGenerator\Exception\RuntimeGenerationException - when problems are encountered during generation
         * @return string - Shift-JIS encoded zengin-formatted file
         */
        public function generateNoValidation(TransferRequest $request) {
                $lines = array();

                try {
                        $lines[] = $this->generateHeaderLine($request);

                        foreach ($request->getTransactions() as $transaction) {
                                $lines[] = $this->generateTransactionLine($transaction);
                        }

                        $lines[] = $this->generateTrailerLine($request);

                        $lines[] = $this->generateEndLine($request);
                } catch (\InvalidArgumentException $e) {
                        throw new \Devture\ZenginGenerator\Exception\RuntimeGenerationException($e->getMessage(), null, $e);
                } catch (\RuntimeException $e) {
                        throw new \Devture\ZenginGenerator\Exception\RuntimeGenerationException($e->getMessage(), null, $e);
                }

                $content = implode("\r\n", $lines);
                $contentShiftJis = mb_convert_encoding($content, 'cp932', mb_detect_encoding($content));

                return $contentShiftJis;
        }

        private function generateHeaderLine(TransferRequest $request) {
                $items = array();
                $items[] = array('%1d', self::RECORD_HEADER);
                $items[] = array('%2d', self::resolveTransferRequestType($request->getType()));
                $items[] = array('%1d', self::CHARSET_JIS);
                $items[] = array('%010d', $request->getSourceBankAccount()->getCompanyCode());

                $holderName = $request->getSourceBankAccount()->getHolderName();
                $holderNameHalfWidth = StringUtil::convertFullWidthToHalfWidthKana($holderName);
                $items[] = array('%s', StringUtil::stringPadRight($holderNameHalfWidth, 40, ' '));

                $items[] = array('%02d', $request->getDate()->format('m'));
                $items[] = array('%02d', $request->getDate()->format('d'));
                $items[] = array('%04d', $request->getSourceBankAccount()->getBankCode());

                $bankName = $request->getSourceBankAccount()->getBankName();
                $bankNameHalfWidth = StringUtil::convertFullWidthToHalfWidthKana($bankName);
                $items[] = array('%s', StringUtil::stringPadRight($bankNameHalfWidth, 15, ' '));

                $items[] = array('%03d', $request->getSourceBankAccount()->getBranchCode());

                $branchName = $request->getSourceBankAccount()->getBranchName();
                $branchNameHalfWidth = StringUtil::convertFullWidthToHalfWidthKana($branchName);
                $items[] = array('%s', StringUtil::stringPadRight($branchNameHalfWidth, 15, ' '));

                $items[] = array('%1d', self::resolveBankAccountType($request->getSourceBankAccount()->getType()));
                $items[] = array('%07d', $request->getSourceBankAccount()->getNumber());

                $items[] = array('%s', str_repeat(' ', 17));

                return StringUtil::sprintfItemGroupsToString($items);
        }

        private function generateTransactionLine(MoneyTransferTransaction $transaction) {
                $items = array();
                $items[] = array('%1d', self::RECORD_DATA);
                $items[] = array('%04d', $transaction->getDestinationBankAccount()->getBankCode());

                $bankName = $transaction->getDestinationBankAccount()->getBankName();
                $bankNameHalfWidth = StringUtil::convertFullWidthToHalfWidthKana($bankName);
                $items[] = array('%s', StringUtil::stringPadRight($bankNameHalfWidth, 15, ' '));

                $items[] = array('%03d', $transaction->getDestinationBankAccount()->getBranchCode());

                $branchName = $transaction->getDestinationBankAccount()->getBranchName();
                $branchNameHalfWidth = StringUtil::convertFullWidthToHalfWidthKana($branchName);
                $items[] = array('%s', StringUtil::stringPadRight($branchNameHalfWidth, 15, ' '));

                //Clearing-house number
                $items[] = array('%04d', 0);

                $items[] = array('%1d', self::resolveBankAccountType($transaction->getDestinationBankAccount()->getType()));

                $items[] = array('%07d', $transaction->getDestinationBankAccount()->getNumber());

                $holderName = $transaction->getDestinationBankAccount()->getHolderName();
                $holderNameHalfWidth = StringUtil::convertFullWidthToHalfWidthKana($holderName);
                $items[] = array('%s', StringUtil::stringPadRight($holderNameHalfWidth, 30, ' '));

                $items[] = array('%010d', $transaction->getAmount());

                //New Code: 1 (new), 2 (change), 0 (other)
                $items[] = array('%d', 0);

                if ($transaction->getMemberCode()) {
                        $items[] = array('%010d', $transaction->getMemberCode());
                } else {
                        $items[] = array('%s', str_repeat(' ', 10));
                }

                if ($transaction->getAffiliationCode()) {
                        $items[] = array('%010d', $transaction->getAffiliationCode());
                } else {
                        $items[] = array('%s', str_repeat(' ', 10));
                }

                //Transfer designation classification: 7 (Tele-Exchange)
                $items[] = array('%d', 7);

                //Identification
                $items[] = array('%s', ' ');

                $items[] = array('%s', str_repeat(' ', 7));

                return StringUtil::sprintfItemGroupsToString($items);
        }

        private function generateTrailerLine(TransferRequest $request) {
                $items = array();
                $items[] = array('%1d', self::RECORD_TRAILER);
                $items[] = array('%06d', count($request->getTransactions()));

                $totalAmount = array_sum(array_map(function (MoneyTransferTransaction $transaction) {
                        return $transaction->getAmount();
                }, $request->getTransactions()));
                $items[] = array('%012d', $totalAmount);

                $items[] = array('%s', str_repeat(' ', 101));

                return StringUtil::sprintfItemGroupsToString($items);
        }

        private function generateEndLine(TransferRequest $request) {
                $items = array();
                $items[] = array('%1d', self::RECORD_END);
                $items[] = array('%s', str_repeat(' ', 119));
                return StringUtil::sprintfItemGroupsToString($items);
        }

        private static function resolveTransferRequestType($transferRequestType) {
                if ($transferRequestType === TransferRequest::TYPE_GENERAL) {
                        return 21;
                }
                if ($transferRequestType === TransferRequest::TYPE_SALARY) {
                        return 11;
                }
                if ($transferRequestType === TransferRequest::TYPE_REWARD) {
                        return 12;
                }
                throw new \InvalidArgumentException(sprintf('Cannot resolve transfer request type for: %s', $transferRequestType));
        }

        private static function resolveBankAccountType($bankAccountType) {
                if ($bankAccountType === BankAccount::TYPE_NORMAL) {
                        return 1;
                }
                if ($bankAccountType === BankAccount::TYPE_CURRENT) {
                        return 2;
                }
                throw new \InvalidArgumentException(sprintf('Cannot resolve bank account type for: %s', $bankAccountType));
        }

        private static function stringPadRight($string, $targetLength, $padChar) {
                $stringLength = mb_strlen($string, mb_detect_encoding($string));
                if ($stringLength > $targetLength) {
                        throw new \RuntimeException(sprintf(
                                'String `%s` is %d characters long. Cannot pad to %d.',
                                $string,
                                $stringLength,
                                $targetLength
                        ));
                }
                return $string . str_repeat($padChar, ($targetLength - $stringLength));
        }

}
