<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923090552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "users" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "users" (email)');
        $this->addSql('COMMENT ON COLUMN "users".id IS \'(DC2Type:uuid)\'');
        $this->addAdmin();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "users"');
    }

    private function addAdmin(): void
    {
        $this->addSql("
            INSERT INTO \"users\" (id, email, password, roles, created_at, updated_at)
            VALUES (
                gen_random_uuid(), 
                'admin@example.com', 
                '" . $this->getHashedPassword('Password123') . "',
                '[\"ROLE_ADMIN\", \"ROLE_USER\"]',
                now(),
                now()
            )
        ");
    }

    private function getHashedPassword(string $plain): string
    {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt']
        ]);
        $hasher = $factory->getPasswordHasher('common');

        return $hasher->hash($plain);
    }
}
