<?php

declare(strict_types=1);

namespace Tests\integration\Core\Database;

use Tests\FrameworkTestCase;
use Snicco\Database\MysqliConnection;
use Snicco\Testing\WithDatabaseExceptions;
use Snicco\EloquentBundle\DatabaseServiceProvider;
use Snicco\Database\Contracts\MysqliDriverInterface;
use Tests\integration\database\Concerns\AssertableWpDB;
use Tests\integration\database\Concerns\WithTestTables;
use Tests\integration\database\Concerns\WithTestTransactions;

use function class_uses_recursive;

use const DB_NAME;

class DatabaseTestCase extends FrameworkTestCase
{
    
    use WithDatabaseExceptions;
    
    protected function setUp() :void
    {
        $traits = class_uses_recursive(static::class);
        
        if (in_array(WithTestTables::class, $traits)) {
            $this->afterApplicationBooted(function () {
                $this->removeWpBrowserTransaction();
                $this->withNewTables();
            });
        }
        
        if (in_array(WithTestTransactions::class, $traits)) {
            $this->afterApplicationBooted(function () {
                $this->beginTransaction();
            });
            
            $this->beforeApplicationDestroyed(function () {
                $this->rollbackTransaction();
            });
        }
        
        parent::setUp();
    }
    
    protected function packageProviders() :array
    {
        return [
            DatabaseServiceProvider::class,
        ];
    }
    
    protected function removeWpBrowserTransaction()
    {
        global $wpdb;
        $wpdb->query('COMMIT');
    }
    
    /**
     * NOTE: THIS DATABASE HAS TO EXIST ON THE LOCAL MACHINE.
     */
    protected function secondDatabaseConfig() :array
    {
        return [
            'username' => $_SERVER['SECONDARY_DB_USER'] ?? 'root',
            'database' => $_SERVER['SECONDARY_DB_NAME'],
            'password' => $_SERVER['SECONDARY_DB_PASSWORD'] ?? '',
            'host' => $_SERVER['SECONDARY_DB_HOST'] ?? '127.0.0.1',
        ];
    }
    
    protected function assertDefaultConnection(MysqliDriverInterface $wpdb) :void
    {
        $this->assertSame(DB_NAME, $wpdb->dbname);
    }
    
    protected function assertNotDefaultConnection(MysqliDriverInterface $wpdb) :void
    {
        $this->assertNotSame(DB_NAME, $wpdb->dbname);
    }
    
    protected function withFakeDb() :DatabaseTestCase
    {
        $this->instance(MysqliDriverInterface::class, FakeDB::class);
        return $this;
    }
    
    protected function assertable(MysqliConnection $connection) :FakeDB
    {
        return $connection->dbInstance();
    }
    
    protected function wpdbInsert(string $table, array $data)
    {
        global $wpdb;
        
        $format = $this->format($data);
        
        $success = $wpdb->insert($table, $data, $format) !== false;
        
        if ( ! $success) {
            $this->fail('Failed to insert with wpdb for test setup.');
        }
    }
    
    protected function wpdbUpdate(string $table, array $data, array $where)
    {
        global $wpdb;
        
        $where_format = $this->format($where);
        $data_format = $this->format($data);
        
        $success = $wpdb->update($table, $data, $where, $data_format, $where_format) !== false;
        
        if ( ! $success) {
            $this->fail('Failed to update with wpdb.');
        }
    }
    
    protected function wpdbDelete(string $table, array $wheres)
    {
        global $wpdb;
        
        $wpdb->delete($table, $wheres, $this->format($wheres));
    }
    
    protected function assertDbTable(string $table_name) :AssertableWpDB
    {
        return new AssertableWpDB($table_name);
    }
    
    private function format(array $data)
    {
        $format = [];
        foreach ($data as $item) {
            if (is_float($item)) {
                $format[] = "%f";
            }
            
            if (is_int($item)) {
                $format[] = "%d";
            }
            
            if (is_string($item)) {
                $format[] = "%s";
            }
        }
        
        return $format;
    }
    
}