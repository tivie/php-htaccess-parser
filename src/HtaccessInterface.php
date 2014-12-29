<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessInterface.php created at 03-12-2014
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


namespace Tivie\HtaccessParser;

/**
 * Interface HtaccessInterface
 *
 * @package Tivie\HtaccessParser
 * @copyright 2014 Estevão Soares dos Santos
 */
interface HtaccessInterface extends \JsonSerializable, \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    /**
     * Returns a representation of the htaccess, ready for inclusion in a file
     *
     * @api
     * @return string
     */
    public function txtSerialize();

    /**
     * Get a string representation of this ArrayObject
     *
     * @api
     * @return string
     */
    public function __toString();
}
