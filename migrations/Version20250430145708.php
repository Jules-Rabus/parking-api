<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430145708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE batch (id SERIAL NOT NULL, status VARCHAR(255) NOT NULL, file_id UUID DEFAULT NULL, mistral_id UUID DEFAULT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN batch.file_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN batch.mistral_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN batch.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE batch_message (batch_id INT NOT NULL, message_id INT NOT NULL, PRIMARY KEY(batch_id, message_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E66BBA25F39EBE7A ON batch_message (batch_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E66BBA25537A1329 ON batch_message (message_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message (id SERIAL NOT NULL, phone_id INT DEFAULT NULL, content TEXT NOT NULL, status VARCHAR(255) NOT NULL, cases JSON DEFAULT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B6BD307F3B7323CB ON message (phone_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN message.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE password_token (id SERIAL NOT NULL, user_id INT NOT NULL, token VARCHAR(50) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_BEAB6C245F37A13B ON password_token (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BEAB6C24A76ED395 ON password_token (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE phone (id SERIAL NOT NULL, owner_id INT NOT NULL, phone_number VARCHAR(255) NOT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_444F97DD7E3C61F9 ON phone (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN phone.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE batch_message ADD CONSTRAINT FK_E66BBA25F39EBE7A FOREIGN KEY (batch_id) REFERENCES batch (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE batch_message ADD CONSTRAINT FK_E66BBA25537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307F3B7323CB FOREIGN KEY (phone_id) REFERENCES phone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_token ADD CONSTRAINT FK_BEAB6C24A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE phone ADD CONSTRAINT FK_444F97DD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD message_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD holder_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955537A1329 FOREIGN KEY (message_id) REFERENCES message (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955DEEE62D0 FOREIGN KEY (holder_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_42C84955537A1329 ON reservation (message_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C84955DEEE62D0 ON reservation (holder_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT FK_42C84955537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE batch_message DROP CONSTRAINT FK_E66BBA25F39EBE7A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE batch_message DROP CONSTRAINT FK_E66BBA25537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP CONSTRAINT FK_B6BD307F3B7323CB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_token DROP CONSTRAINT FK_BEAB6C24A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE phone DROP CONSTRAINT FK_444F97DD7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE batch
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE batch_message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE password_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE phone
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT FK_42C84955DEEE62D0
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_42C84955537A1329
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42C84955DEEE62D0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP message_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP holder_id
        SQL);
    }
}
