<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713214522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anime ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD average_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD format VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD banner_image TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE anime DROP description');
        $this->addSql('ALTER TABLE anime DROP average_score');
        $this->addSql('ALTER TABLE anime DROP duration');
        $this->addSql('ALTER TABLE anime DROP status');
        $this->addSql('ALTER TABLE anime DROP format');
        $this->addSql('ALTER TABLE anime DROP banner_image');
    }
}
