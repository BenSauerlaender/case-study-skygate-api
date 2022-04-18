<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\BaseDatabaseTest;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection
 */
abstract class BaseMySqlAccessorTest extends BaseDatabaseTest
{
    /**
     * Deletes and re-creates the database and tables
     */
    protected static function resetDB(): void
    {
        self::$pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['DB_DATABASE'] . ";");

        //create tables
        MySqlTableCreator::create(self::$pdo);
    }

    /**
     * Counts changed rows and performs an "assertEquals" on the expected and actual result
     * 
     * @param int $expected The expected number of changed rows
     */
    protected function assertChangedRowsEquals(int $expected): void
    {

        self::$pdo->exec('
            //TODO
        ');

        $changedRows;

        $this->assertEquals($expected, $changedRows);
    }
}