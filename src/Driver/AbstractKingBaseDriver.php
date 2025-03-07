<?php

namespace Doctrine\DBAL\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\API\PostgreSQL;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\KingBase100Platform;
use Doctrine\DBAL\Platforms\KingBase94Platform;
use Doctrine\DBAL\Platforms\KingBasePlatform;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\DBAL\VersionAwarePlatformDriver;
use Doctrine\Deprecations\Deprecation;

use function assert;
use function preg_match;
use function version_compare;

/**
 * Abstract base implementation of the {@see Driver} interface for PostgreSQL based drivers.
 */
abstract class AbstractKingBaseDriver implements VersionAwarePlatformDriver
{
    /**
     * {@inheritDoc}
     */
    public function createDatabasePlatformForVersion($version)
    {
        if (preg_match('/^(?P<major>\d+)(?:\.(?P<minor>\d+)(?:\.(?P<patch>\d+))?)?/', $version, $versionParts) === 0) {
            throw Exception::invalidPlatformVersionSpecified(
                $version,
                '<major_version>.<minor_version>.<patch_version>',
            );
        }

        $majorVersion = $versionParts['major'];
        $minorVersion = $versionParts['minor'] ?? 0;
        $patchVersion = $versionParts['patch'] ?? 0;
        $version      = $majorVersion . '.' . $minorVersion . '.' . $patchVersion;

        if (version_compare($version, '10.0', '>=')) {
            return new KingBase100Platform();
        }

        Deprecation::trigger(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/5060',
            'PostgreSQL 9 support is deprecated and will be removed in DBAL 4.'
                . ' Consider upgrading to Postgres 10 or later.',
        );

        return new KingBase94Platform();
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabasePlatform()
    {
        return new KingBase94Platform();
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use {@link KingBasePlatform::createSchemaManager()} instead.
     */
    public function getSchemaManager(Connection $conn, AbstractPlatform $platform)
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/5458',
            'AbstractPostgreSQLDriver::getSchemaManager() is deprecated.'
                . ' Use KingBasePlatform::createSchemaManager() instead.',
        );

        assert($platform instanceof KingBasePlatform);

        return new PostgreSQLSchemaManager($conn, $platform);
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return new PostgreSQL\ExceptionConverter();
    }
}
