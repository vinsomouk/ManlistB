<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702105421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anime (id INT NOT NULL, title VARCHAR(255) NOT NULL, image_url VARCHAR(255) DEFAULT NULL, episode_count INT DEFAULT NULL, raw_data JSON DEFAULT NULL, last_synced_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN anime.last_synced_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE anime_genres (anime_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(anime_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_1EE1614B794BBE89 ON anime_genres (anime_id)');
        $this->addSql('CREATE INDEX IDX_1EE1614B4296D31F ON anime_genres (genre_id)');
        $this->addSql('CREATE TABLE answer_option (id SERIAL NOT NULL, question_id INT NOT NULL, text TEXT NOT NULL, tags JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A87F3A171E27F6BF ON answer_option (question_id)');
        $this->addSql('CREATE TABLE genre (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_835033F85E237E06 ON genre (name)');
        $this->addSql('CREATE TABLE question (id SERIAL NOT NULL, questionnaire_id INT NOT NULL, text TEXT NOT NULL, "order" INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494ECE07E8FF ON question (questionnaire_id)');
        $this->addSql('CREATE TABLE questionnaire (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE response_item (id SERIAL NOT NULL, user_response_id INT NOT NULL, question_id INT NOT NULL, answer_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B3BA2F8452E8E1D5 ON response_item (user_response_id)');
        $this->addSql('CREATE INDEX IDX_B3BA2F841E27F6BF ON response_item (question_id)');
        $this->addSql('CREATE INDEX IDX_B3BA2F84AA334807 ON response_item (answer_id)');
        $this->addSql('CREATE TABLE tag (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nickname VARCHAR(255) NOT NULL, profile_picture VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_verified BOOLEAN NOT NULL, reset_token VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_response (id SERIAL NOT NULL, user_id INT NOT NULL, questionnaire_id INT NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DEF6EFFBA76ED395 ON user_response (user_id)');
        $this->addSql('CREATE INDEX IDX_DEF6EFFBCE07E8FF ON user_response (questionnaire_id)');
        $this->addSql('COMMENT ON COLUMN user_response.completed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE watchlist (id SERIAL NOT NULL, user_id INT NOT NULL, anime_id INT NOT NULL, status VARCHAR(20) NOT NULL, progress INT NOT NULL, score INT DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_340388D3A76ED395 ON watchlist (user_id)');
        $this->addSql('CREATE INDEX IDX_340388D3794BBE89 ON watchlist (anime_id)');
        $this->addSql('COMMENT ON COLUMN watchlist.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN watchlist.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE anime_genres ADD CONSTRAINT FK_1EE1614B794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE anime_genres ADD CONSTRAINT FK_1EE1614B4296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer_option ADD CONSTRAINT FK_A87F3A171E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_item ADD CONSTRAINT FK_B3BA2F8452E8E1D5 FOREIGN KEY (user_response_id) REFERENCES user_response (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_item ADD CONSTRAINT FK_B3BA2F841E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_item ADD CONSTRAINT FK_B3BA2F84AA334807 FOREIGN KEY (answer_id) REFERENCES answer_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_response ADD CONSTRAINT FK_DEF6EFFBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_response ADD CONSTRAINT FK_DEF6EFFBCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE watchlist ADD CONSTRAINT FK_340388D3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE watchlist ADD CONSTRAINT FK_340388D3794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE anime_genres DROP CONSTRAINT FK_1EE1614B794BBE89');
        $this->addSql('ALTER TABLE anime_genres DROP CONSTRAINT FK_1EE1614B4296D31F');
        $this->addSql('ALTER TABLE answer_option DROP CONSTRAINT FK_A87F3A171E27F6BF');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECE07E8FF');
        $this->addSql('ALTER TABLE response_item DROP CONSTRAINT FK_B3BA2F8452E8E1D5');
        $this->addSql('ALTER TABLE response_item DROP CONSTRAINT FK_B3BA2F841E27F6BF');
        $this->addSql('ALTER TABLE response_item DROP CONSTRAINT FK_B3BA2F84AA334807');
        $this->addSql('ALTER TABLE user_response DROP CONSTRAINT FK_DEF6EFFBA76ED395');
        $this->addSql('ALTER TABLE user_response DROP CONSTRAINT FK_DEF6EFFBCE07E8FF');
        $this->addSql('ALTER TABLE watchlist DROP CONSTRAINT FK_340388D3A76ED395');
        $this->addSql('ALTER TABLE watchlist DROP CONSTRAINT FK_340388D3794BBE89');
        $this->addSql('DROP TABLE anime');
        $this->addSql('DROP TABLE anime_genres');
        $this->addSql('DROP TABLE answer_option');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE response_item');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_response');
        $this->addSql('DROP TABLE watchlist');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
