<?php
namespace Devture\ZenginGenerator\Exception;

use Devture\Component\Form\Validator\ViolationsList;

class PrecheckGenerationException extends GenerationException {

        private $violations;

        public function __construct($message, ViolationsList $violations) {
                parent::__construct($message);
        }

        public function getViolations() {
                return $this->violations;
        }

}
