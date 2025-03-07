<?php

namespace Doctrine\DBAL\Driver\KingBase\Exception;

use Doctrine\DBAL\Driver\AbstractException;

use function sprintf;

/** @psalm-immutable */
final class UnknownParameter extends AbstractException
{
    public static function new(string $param): self
    {
        return new self(
            sprintf('Could not find parameter %s in the SQL statement', $param),
        );
    }
}
