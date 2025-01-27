<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Database;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ShowTableInfoTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private $streamFilter;
    protected $migrateOnce = true;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        stream_filter_remove($this->streamFilter);
    }

    private function getResultWithoutControlCode(): string
    {
        return str_replace(
            ["\033[0;30m", "\033[0;33m", "\033[43m", "\033[0m"],
            '',
            CITestStreamFilter::$buffer
        );
    }

    public function testDbTable(): void
    {
        command('db:table db_migrations');

        $result = $this->getResultWithoutControlCode();

        $expected = 'Data of Table "db_migrations":';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+----------------+--------------------+-------+---------------+------------+-------+
            | id | version        | class              | group | namespace     | time       | batch |
            +----+----------------+--------------------+-------+---------------+------------+-------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableShow(): void
    {
        command('db:table --show');

        $result = $this->getResultWithoutControlCode();

        $expected = 'The following is a list of the names of all database tables:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+---------------------------+-------------+---------------+
            | ID | Table Name                | Num of Rows | Num of Fields |
            +----+---------------------------+-------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableMetadata(): void
    {
        command('db:table db_migrations --metadata');

        $result = $this->getResultWithoutControlCode();

        $expected = 'List of Metadata Information in Table "db_migrations":';
        $this->assertStringContainsString($expected, $result);

        $result   = preg_replace('/\s+/', ' ', $result);
        $expected = <<<'EOL'
            | Field Name | Type | Max Length | Nullable | Default | Primary Key |
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableDesc(): void
    {
        $seeder = Database::seeder();
        $seeder->call(CITestSeeder::class);

        command('db:table db_user --desc');

        $result = $this->getResultWithoutControlCode();

        $expected = 'Data of Table "db_user":';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+--------------------+--------------------+---------+------------+------------+------------+
            | id | name               | email              | country | created_at | updated_at | deleted_at |
            +----+--------------------+--------------------+---------+------------+------------+------------+
            | 4  | Chris Martin       | chris@world.com    | UK      |            |            |            |
            | 3  | Richard A Cause... | richard@world.c... | US      |            |            |            |
            | 2  | Ahmadinejad        | ahmadinejad@wor... | Iran    |            |            |            |
            | 1  | Derek Jones        | derek@world.com    | US      |            |            |            |
            +----+--------------------+--------------------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableLimitFieldValueLength(): void
    {
        command('db:table db_user --limit-field-value 5');

        $result = $this->getResultWithoutControlCode();

        $expected = 'Data of Table "db_user":';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+----------+----------+---------+------------+------------+------------+
            | id | name     | email    | country | created_at | updated_at | deleted_at |
            +----+----------+----------+---------+------------+------------+------------+
            | 1  | Derek... | derek... | US      |            |            |            |
            | 2  | Ahmad... | ahmad... | Iran    |            |            |            |
            | 3  | Richa... | richa... | US      |            |            |            |
            | 4  | Chris... | chris... | UK      |            |            |            |
            +----+----------+----------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableLimitRows(): void
    {
        command('db:table db_user --limit-rows 2');

        $result = $this->getResultWithoutControlCode();

        $expected = 'Data of Table "db_user":';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+-------------+--------------------+---------+------------+------------+------------+
            | id | name        | email              | country | created_at | updated_at | deleted_at |
            +----+-------------+--------------------+---------+------------+------------+------------+
            | 1  | Derek Jones | derek@world.com    | US      |            |            |            |
            | 2  | Ahmadinejad | ahmadinejad@wor... | Iran    |            |            |            |
            +----+-------------+--------------------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableAllOptions(): void
    {
        command('db:table db_user --limit-rows 2 --limit-field-value 5 --desc');

        $result = $this->getResultWithoutControlCode();

        $expected = 'Data of Table "db_user":';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+----------+----------+---------+------------+------------+------------+
            | id | name     | email    | country | created_at | updated_at | deleted_at |
            +----+----------+----------+---------+------------+------------+------------+
            | 4  | Chris... | chris... | UK      |            |            |            |
            | 3  | Richa... | richa... | US      |            |            |            |
            +----+----------+----------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }
}
