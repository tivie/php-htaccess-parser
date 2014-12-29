<?php
/**
 * -- PHP Htaccess Parser --
 * Exception.php created at 02-12-2014
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
 * Class Exception
 * A generic exception thrown by HtaccessParser
 *
 * Note: This exception and all its children change the line and file in the backtrace log. If you
 *
 * @package Tivie\HtaccessParser\Exception
 * @copyright 2014 Estevão Soares dos Santos
 */
class Exception extends \Exception
{
    /**
     * @var bool
     */
    protected static $debugLibrary = false;

    /**
     * Create a new generic HtaccessParser Exception
     *
     * @param string $message [optional] Exception message
     * @param int $code [optional] Code of the exception
     * @param \Exception $exception [optional] Previous Exception
     */
    public function __construct($message = null, $code = 0, \Exception $exception = null)
    {
        if (!self::$debugLibrary) {
            $this->changeLineAndFile();
        }
        parent::__construct($message, $code, $exception);
    }

    private function changeLineAndFile()
    {
        $trace = $this->getTrace();
        if (!isset($trace[0])) {
            return;
        } else {
            $trace = $trace[0];
        }

        if (isset($trace['file']) && isset($trace['line'])) {
            $this->file = $trace['file'];
            $this->line = $trace['line'];
        }
    }

    /**
     * If you wish to debug the Htaccess Parser Library itself, you can set this flag to true. This will prevent
     * this exception (and its children) changing the line and file in the backtrace log.
     *
     * @param bool $debugLibrary
     */
    public static function debugLibrary($debugLibrary)
    {
        self::$debugLibrary = !!$debugLibrary;
    }
}
