<?php
/**
 * -- PHP Htaccess Parser --
 * DomainException.php created at 02-12-2014
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
 * Class DomainException
 * Exception thrown if a value does not adhere to a defined valid data domain.
 *
 * @package Tivie\HtaccessParser\Exception
 * @copyright 2014 Estevão Soares dos Santos
 */
class DomainException extends Exception
{
    /**
     * Exception thrown if a value does not adhere to a defined valid data domain.
     *
     * @param string $message [optional] Exception message
     * @param int $code [optional] Code of the exception
     * @param \Exception $exception [optional] Previous Exception
     */
    public function __construct($message = '', $code = 0, \Exception $exception = null)
    {
        parent::__construct($message, $code, $exception);
    }
}
