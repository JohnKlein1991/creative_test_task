<?php

declare(strict_types=1);

namespace App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200830105839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating "movie" table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            create table movie
            (
                id serial primary key,
                title varchar(255) not null,
                link varchar(255) not null,
                description longtext not null,
                pub_date datetime not null,
                image varchar(255) null
            );
        ');

        $this->addSql('
            create index IDX_1D5EF26F2B36786B on movie (title);
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            drop table movie;
        ');
    }
}
