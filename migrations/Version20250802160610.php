<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250802160610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anime_genres (anime_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(anime_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_1EE1614B794BBE89 ON anime_genres (anime_id)');
        $this->addSql('CREATE INDEX IDX_1EE1614B4296D31F ON anime_genres (genre_id)');
        $this->addSql('CREATE TABLE genre (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_835033F85E237E06 ON genre (name)');
        $this->addSql('CREATE TABLE response_item (id SERIAL NOT NULL, user_response_id INT NOT NULL, question_id INT NOT NULL, answer_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B3BA2F8452E8E1D5 ON response_item (user_response_id)');
        $this->addSql('CREATE INDEX IDX_B3BA2F841E27F6BF ON response_item (question_id)');
        $this->addSql('CREATE INDEX IDX_B3BA2F84AA334807 ON response_item (answer_id)');
        $this->addSql('CREATE TABLE tag (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)');
        $this->addSql('ALTER TABLE anime_genres ADD CONSTRAINT FK_1EE1614B794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE anime_genres ADD CONSTRAINT FK_1EE1614B4296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_item ADD CONSTRAINT FK_B3BA2F8452E8E1D5 FOREIGN KEY (user_response_id) REFERENCES user_response (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_item ADD CONSTRAINT FK_B3BA2F841E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_item ADD CONSTRAINT FK_B3BA2F84AA334807 FOREIGN KEY (answer_id) REFERENCES answer_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_response DROP answers');
        $this->addSql('ALTER TABLE watchlist DROP CONSTRAINT FK_340388D3A76ED395');
        $this->addSql('ALTER TABLE watchlist ADD CONSTRAINT FK_340388D3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE anime_genres DROP CONSTRAINT FK_1EE1614B794BBE89');
        $this->addSql('ALTER TABLE anime_genres DROP CONSTRAINT FK_1EE1614B4296D31F');
        $this->addSql('ALTER TABLE response_item DROP CONSTRAINT FK_B3BA2F8452E8E1D5');
        $this->addSql('ALTER TABLE response_item DROP CONSTRAINT FK_B3BA2F841E27F6BF');
        $this->addSql('ALTER TABLE response_item DROP CONSTRAINT FK_B3BA2F84AA334807');
        $this->addSql('DROP TABLE anime_genres');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE response_item');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE watchlist DROP CONSTRAINT fk_340388d3a76ed395');
        $this->addSql('ALTER TABLE watchlist ADD CONSTRAINT fk_340388d3a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_response ADD answers JSON NOT NULL');
    }
}
