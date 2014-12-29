<?php
/**
 * -- PHP Htaccess Parser --
 * InvalidArgumentException.php created at 02-12-2014
 *
 * Copyright 2014 Estevão Soares dos Santos
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

namespace Tivie\HtaccessParser\Exception;

/**
 * Class InvalidArgumentException
 * Exception thrown if an argument is not of the expected type.
 *
 * @package Tivie\HtaccessParser\Exception
 * @copyright 2014 Estevão Soares dos Santos
 */
class InvalidArgumentException extends Exception
{
    /**
     * Create a new InvalidArgumentException, an exception thrown if an argument is not of the expected type.
     *
     * @param string $expectedType [required] The type expected
     * @param int $argNum [required] The argument number that failed the type check. Note: $argNum is zero
     *                                            based, that means if you wish to refer to the first argument, you
     *                                            should pass 0 and not 1.
     * @param string $message [optional] Exception message
     * @param int $code [optional] Code of the exception
     * @param \Exception $exception [optional] Previous Exception
     */
    public function __construct($expectedType, $argNum, $message = null, $code = 0, \Exception $exception = null)
    {
        if (is_string($expectedType) && is_int($argNum)) {
            $trace = $this->getTrace();
            if (isset($trace[0])) {
                $message = $this->buildMsg($trace[0], $expectedType, $argNum);
            } else {
                $message = "Expected argument $argNum to be $expectedType";
            }
        }

        parent::__construct($message, $code, $exception);
    }

    private function buildMsg($trace, $expectedType, $argNum)
    {
        $msg = '';

        if (isset($trace['class']) && $trace['type'] && $trace['function']) {
            $msg .= "{$trace['class']}{$trace['type']}{$trace['function']} ";
        } else {
            if ($trace['function']) {
                $msg .= $trace['function'] . " ";
            }
        }

        $msg .= 'expected ';

        if (is_int($argNum)) {
            $msg .= 'argument ' . ($argNum + 1) . ' to be ';
        }

        if (is_string($expectedType)) {
            $msg .= " of type $expectedType ";
        } else {
            $msg .= " something else";
        }

        if (isset($trace['args'][$argNum])) {
            $msg .= ", but got " . gettype($trace['args'][$argNum]) . " instead.";
        } else {
            $msg .= '.';
        }

        return $msg;
    }
}
