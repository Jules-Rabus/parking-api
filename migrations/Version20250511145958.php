<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511145958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE client_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE password_reset_token_id_seq CASCADE
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
            ALTER TABLE password_token ADD CONSTRAINT FK_BEAB6C24A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token DROP CONSTRAINT fk_6b7ba4b6a76ed395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE password_reset_token
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date ADD created_at DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN date.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_date RENAME TO UNIQ_AA9E377AAA9E377A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD cases JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_444F97DD6B01BC5B ON phone (phone_number)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD holder_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955DEEE62D0 FOREIGN KEY (holder_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C84955DEEE62D0 ON reservation (holder_id)
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
            CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE password_reset_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE client (id SERIAL NOT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, is_verified BOOLEAN NOT NULL, telephone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_c7440455e7927c74 ON client (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE password_reset_token (id SERIAL NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, hashed_token VARCHAR(100) NOT NULL, selector VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_6b7ba4b6a76ed395 ON password_reset_token (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ADD CONSTRAINT fk_6b7ba4b6a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_token DROP CONSTRAINT FK_BEAB6C24A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE password_token
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP cases
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_444F97DD6B01BC5B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT FK_42C84955DEEE62D0
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42C84955DEEE62D0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP holder_id
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
            ALTER TABLE date DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_aa9e377aaa9e377a RENAME TO uniq_date
        SQL);
    }
}
