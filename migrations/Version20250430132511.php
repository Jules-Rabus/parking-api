<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430132511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE date (id SERIAL NOT NULL, date DATE NOT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AA9E377AAA9E377A ON date (date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_DATE ON date (date)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN date.date IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN date.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE date_reservation (date_id INT NOT NULL, reservation_id INT NOT NULL, PRIMARY KEY(date_id, reservation_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7DB17C89B897366B ON date_reservation (date_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7DB17C89B83297E7 ON date_reservation (reservation_id)
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
            CREATE TABLE reservation (id SERIAL NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, vehicle_count INT NOT NULL, status VARCHAR(255) NOT NULL, booking_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN reservation.booking_date IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN reservation.created_at IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date_reservation ADD CONSTRAINT FK_7DB17C89B897366B FOREIGN KEY (date_id) REFERENCES date (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date_reservation ADD CONSTRAINT FK_7DB17C89B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_token ADD CONSTRAINT FK_BEAB6C24A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date_reservation DROP CONSTRAINT FK_7DB17C89B897366B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE date_reservation DROP CONSTRAINT FK_7DB17C89B83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_token DROP CONSTRAINT FK_BEAB6C24A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE date
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE date_reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE password_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
