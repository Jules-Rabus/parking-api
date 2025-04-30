<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250312111703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE date (id SERIAL NOT NULL, date DATE NOT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AA9E377AAA9E377A ON date (date)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DATE ON date (date)');
        $this->addSql('COMMENT ON COLUMN date.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN date.created_at IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE date_reservation (date_id INT NOT NULL, reservation_id INT NOT NULL, PRIMARY KEY(date_id, reservation_id))');
        $this->addSql('CREATE INDEX IDX_7DB17C89B897366B ON date_reservation (date_id)');
        $this->addSql('CREATE INDEX IDX_7DB17C89B83297E7 ON date_reservation (reservation_id)');
        $this->addSql('CREATE TABLE reservation (id SERIAL NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, vehicle_count INT NOT NULL, status VARCHAR(255) NOT NULL, booking_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at DATE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN reservation.booking_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reservation.created_at IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE date_reservation ADD CONSTRAINT FK_7DB17C89B897366B FOREIGN KEY (date_id) REFERENCES date (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE date_reservation ADD CONSTRAINT FK_7DB17C89B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE date_reservation DROP CONSTRAINT FK_7DB17C89B897366B');
        $this->addSql('ALTER TABLE date_reservation DROP CONSTRAINT FK_7DB17C89B83297E7');
        $this->addSql('DROP TABLE date');
        $this->addSql('DROP TABLE date_reservation');
        $this->addSql('DROP TABLE reservation');
    }
}
