<?php

declare(strict_types=1);

namespace ShlinkMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20220319175813 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('short_urls');
        $this->skipIf($table->hasColumn('password'));
        $table->addColumn('password', Types::STRING, [
            'notnull' => false,
            'default' => null,
        ]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('short_urls');
        $this->skipIf(! $table->hasColumn('password'));

        $table->dropColumn('password');
    }

    public function isTransactional(): bool
    {
        return ! $this->isMySql();
    }

    private function isMySql(): bool
    {
        return $this->connection->getDatabasePlatform() instanceof MySQLPlatform;
    }
}
