<?php

declare(strict_types=1);

namespace Sabre\Cache;

/**
 * This exception is thrown if an invalid arugment is passed to one of the
 * caching functions, such as a non-string cache key.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class InvalidArgumentException extends \InvalidArgumentException // PHP built-in
    implements \Psr\SimpleCache\InvalidArgumentException // PSR-16
{
}
