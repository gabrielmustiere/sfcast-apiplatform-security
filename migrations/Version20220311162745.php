<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220311162745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cheese_listing ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE cheese_listing ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE cheese_listing ALTER owner_id TYPE UUID');
        $this->addSql('ALTER TABLE cheese_listing ALTER owner_id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN cheese_listing.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN cheese_listing.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user" ADD phone_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE "user" ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP phone_number');
        $this->addSql('ALTER TABLE "user" ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE "user" ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE cheese_listing ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE cheese_listing ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE cheese_listing ALTER owner_id TYPE UUID');
        $this->addSql('ALTER TABLE cheese_listing ALTER owner_id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN cheese_listing.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN cheese_listing.owner_id IS \'(DC2Type:ulid)\'');
    }
}
