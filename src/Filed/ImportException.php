<?php

namespace Plat\Field;

use Exception;

class ImportException extends Exception {

    public $validator;

    public function __construct($messages = []) {

        $this->messages = $messages;

    }

}