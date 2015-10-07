<?php

/*
 *
 *
 * Copyright (c) 2010 158, Ltd.
 * All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification are not permitted.
 *
 * Neither the name of 158, Ltd. or the names of contributors
 * may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 *
 * This software is provided "AS IS," without a warranty of any kind. ALL
 * EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING
 * ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED. 158 AND ITS LICENSORS
 * SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF
 * USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES. IN NO
 * EVENT WILL 158 OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE, PROFIT
 * OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL, INCIDENTAL OR
 * PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE THEORY OF
 * LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE SOFTWARE, EVEN
 * IF 158 HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.
 *
 * Any violation of the copyright rules above will be punished by the lay.
 */

namespace Lib\Database;

class DataValidation {

    private $errors;
    private $model;

    public function __construct($model) {
        $this->errors = array();
        $this->model = $model;
    }

    public function __destruct() {
        unset($this->errors);
    }

    public function notEmpty($string, $message) {
        $this->normalizeMessage($message);
        if (strlen($string) == 0)
            $this->errors = array_merge($this->errors, $message);
    }

    public function validEmail($string, $message) {
        $this->normalizeMessage($message);
        if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/is', $string)) {
            $this->errors = array_merge($this->errors, $message);
        }
    }

    public function validPhone($string, $message) {
        $this->normalizeMessage($message);
        if (!preg_match('/[0-9\+\-\s]+/i', $string)) {
            $this->errors = array_merge($this->errors, $message);
        }
    }

    public function validCaptcha($string, $captcha, $message) {
        $this->normalizeMessage($message);
        if (md5($string) != $captcha) {
            $this->errors = array_merge($this->errors, $message);
        }
    }

    public function isConfirmed($string, $confirm_strin, $message) {
        $this->normalizeMessage($message);
        if ($string != $confirm_strin) {
            $this->errors = array_merge($this->errors, $message);
        }
    }

    public function moreThan($value, $comparison, $message) {
        if ($value <= $comparison) {
            $this->addError($message);
        }
    }

    public function lessThan($value, $comparison, $message) {
        if ($value >= $comparison) {
            $this->addError($message);
        }
    }

    public function moreOrEqual($value, $comparison, $message) {
        if ($value < $comparison) {
            $this->addError($message);
        }
    }

    public function lessOrEqual($value, $comparison, $message) {
        if ($value > $comparison) {
            $this->addError($message);
        }
    }

    public function condition($condition, $message) {
        if ($condition) {
            $this->addError($message);
        }
    }

    public function unique($lookup, $column, $message) {
        $this->normalizeMessage($message);
        $operation = 'like';
        if (is_numeric($lookup)) {
            $operation = '=';
        }
        $model_object = $this->model;
        $rows = current($model_object->select('count(*) as count_rows')->where("$column $operation ? ")->limit(1)->prepare(array($lookup)));
        if ($rows->count_rows > 0) {
            $this->errors = array_merge($this->errors, $message);
        }
    }
    
    public function match($regExp, $string, $message) {
        $this->normalizeMessage($message);
        
        $this->condition(!preg_match($regExp, $string), $message);
    }

    public function addError($message) {
        $this->normalizeMessage($message);
        $this->errors = array_merge($this->errors, $message);
    }

    public function errors() {
        return $this->errors;
    }

    public function hasErrors() {
        return (sizeof($this->errors) > 0 ? true : false);
    }

    private function normalizeMessage(&$message) {
        if (!is_array($message)) {
            $message = array($message);
        }
    }

}

?>

