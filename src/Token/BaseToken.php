<?php
/**
 * -- PHP Htaccess Parser --
 * BaseToken.php created at 03-12-2014
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

namespace Tivie\HtaccessParser\Token;


use Tivie\HtaccessParser\Exception\DomainException;
use Tivie\HtaccessParser\Exception\InvalidArgumentException;

/**
 * Class BaseToken
 * An abstract class for Tokens to extend
 *
 * @package Tivie\HtaccessParser\Token
 * @copyright 2014 Estevão Soares dos Santos
 */
abstract class BaseToken implements TokenInterface
{

    protected $lineBreaks = array();

    /**
     * Check if this Token spawns across multiple lines
     *
     * @return bool
     */
    public function isMultiLine()
    {
        return (!empty($this->lineBreaks));
    }

    /**
     * Get the line breaks
     *
     * @return int[]
     */
    public function getLineBreaks()
    {
        return $this->lineBreaks;
    }

    /**
     * Set the line breaks
     *
     * @param int[] $lineBreaks Array of integers
     * @throws DomainException
     * @return $this
     */
    public function setLineBreaks(array $lineBreaks)
    {
        foreach ($lineBreaks as $lb) {
            if (!is_int($lb)) {
                throw new DomainException("lineBreaks array is expected to contain only integers");
            }
            $this->lineBreaks[] = $lb;
        }

        return $this;
    }

    /**
     * Add linebreak
     *
     * @param int $lineBreak
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addLineBreak($lineBreak)
    {
        if (!is_int($lineBreak)) {
            throw new InvalidArgumentException('integer', 0);
        }

        $this->lineBreaks[] = $lineBreak;

        return $this;
    }
}
