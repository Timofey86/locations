<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904134506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (id UUID NOT NULL, macro_region_id UUID NOT NULL, iso VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, capital VARCHAR(255) NOT NULL, population INT NOT NULL, phone_code INT NOT NULL, sorting INT DEFAULT NULL, geoname_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5373C9665E237E06 ON country (name)');
        $this->addSql('CREATE INDEX IDX_5373C966F665596C ON country (macro_region_id)');
        $this->addSql('COMMENT ON COLUMN country.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN country.macro_region_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE macro_region (id UUID NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, sorting INT DEFAULT NULL, geoname_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA01CDCD5E237E06 ON macro_region (name)');
        $this->addSql('COMMENT ON COLUMN macro_region.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE region (id UUID NOT NULL, country_id UUID NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, sorting INT DEFAULT NULL, geoname_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F62F1765E237E06 ON region (name)');
        $this->addSql('CREATE INDEX IDX_F62F176F92F3E70 ON region (country_id)');
        $this->addSql('COMMENT ON COLUMN region.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN region.country_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE country ADD CONSTRAINT FK_5373C966F665596C FOREIGN KEY (macro_region_id) REFERENCES macro_region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->fillMacroRegionData();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP CONSTRAINT FK_5373C966F665596C');
        $this->addSql('ALTER TABLE region DROP CONSTRAINT FK_F62F176F92F3E70');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE macro_region');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE messenger_messages');
    }

    private function fillMacroRegionData()
    {
        $rawSql = "INSERT INTO macro_region (
        id,  name, code, sorting, geoname_id, created_at, updated_at
    )
        VALUES
            (gen_random_uuid(), 'Africa', 'AF', NULL, 6255146, now(), now()),
            (gen_random_uuid(), 'Europe', 'EU', NULL, 6255148, now(), now()),
            (gen_random_uuid(), 'Asia', 'AS', NULL, 6255147, now(), now()),
            (gen_random_uuid(), 'North America', 'NA', NULL, 6255149, now(), now()),
            (gen_random_uuid(), 'Oceania', 'OC', NULL, 6255151, now(), now()),
            (gen_random_uuid(), 'Antarctica', 'AN', NULL, 6255152, now(), now()),
            (gen_random_uuid(), 'South America', 'SA', NULL, 6255150, now(), now()),
            (gen_random_uuid(), 'OTHER', 'OTHER', NULL, NULL, now(), now())
            ";
        $this->addSql($rawSql);
    }
}
