<?php

declare(strict_types=1);

namespace Sabre\Cache;

/**
 * This exception is thrown if an invalid argument is passed to one of the
 * caching functions, such as a non-string cache key. It extends the PHP built-in
 * InvalidArgumentException with PSR-16 "Simple Cache".
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class InvalidArgumentException extends \InvalidArgumentException implements \Psr\SimpleCache\InvalidArgumentException
{
}
