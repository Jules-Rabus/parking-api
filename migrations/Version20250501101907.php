<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501101907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_date RENAME TO UNIQ_AA9E377AAA9E377A
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_444F97DD6B01BC5B ON phone (phone_number)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD first_name VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD last_name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER email DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER password DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_identifier_email RENAME TO UNIQ_8D93D649E7927C74
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_444F97DD6B01BC5B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP first_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP last_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER email SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER password SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_8d93d649e7927c74 RENAME TO uniq_identifier_email
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_aa9e377aaa9e377a RENAME TO uniq_date
        SQL);
    }
}
