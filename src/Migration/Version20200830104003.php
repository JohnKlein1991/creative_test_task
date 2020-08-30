<?php

declare(strict_types=1);

namespace App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200830104003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating table "users"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            create table users (
                id serial,
                username varchar(100) unique ,
                password_hash varchar(100),
                created_at datetime,
                updated_at datetime
            );
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            drop table users;
        ');
    }
}
