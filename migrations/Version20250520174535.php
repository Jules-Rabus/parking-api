<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520174535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE code (id SERIAL NOT NULL, content VARCHAR(4) NOT NULL, ajout BOOLEAN DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_77153098FEC530A9 ON code (content)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN code.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD code_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C8495527DAFE17 FOREIGN KEY (code_id) REFERENCES code (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C8495527DAFE17 ON reservation (code_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD code VARCHAR(4) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D64977153098 ON "user" (code)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT FK_42C8495527DAFE17
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE code
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42C8495527DAFE17
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP code_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D64977153098
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP code
        SQL);
    }
}
