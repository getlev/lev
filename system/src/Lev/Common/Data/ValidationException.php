<?php

/**
 * @package    Lev\Grav\Common\Data
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Data;

use Lev\Common\Lev;
use JsonSerializable;
use RuntimeException;

/**
 * Class ValidationException
 * @package Lev\Common\Data
 */
class ValidationException extends RuntimeException implements JsonSerializable
{
    /** @var array */
    protected $messages = [];
    protected $escape = true;

    /**
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages = [])
    {
        $this->messages = $messages;

        $language = Lev::instance()['language'];
        $this->message = $language->translate('LEV.FORM.VALIDATION_FAIL', null, true) . ' ' . $this->message;

        foreach ($messages as $list) {
            $list = array_unique($list);
            foreach ($list as $message) {
                $this->message .= '<br/>' . htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return $this;
    }

    public function setSimpleMessage(bool $escape = true): void
    {
        $first = reset($this->messages);
        $message = reset($first);

        $this->message = $escape ? htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $message;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function jsonSerialize(): array
    {
        return ['validation' => $this->messages];
    }
}
