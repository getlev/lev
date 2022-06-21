<?php

/**
 * @package    Lev\Grav\Framework\Logger
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Logger\Processors;

use Lev\Common\Lev;
use Lev\Common\User\Interfaces\UserInterface;
use Monolog\Processor\ProcessorInterface;

/**
 * Adds username and email to log messages.
 */
class UserProcessor implements ProcessorInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        /** @var UserInterface|null $user */
        $user = Lev::instance()['user'] ?? null;
        if ($user && $user->exists()) {
            $record['extra']['user'] = ['username' => $user->username, 'email' => $user->email];
        }

        return $record;
    }
}
