<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731134258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_option (id SERIAL NOT NULL, question_id INT NOT NULL, text TEXT NOT NULL, tags JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A87F3A171E27F6BF ON answer_option (question_id)');
        $this->addSql('CREATE TABLE question (id SERIAL NOT NULL, questionnaire_id INT NOT NULL, text TEXT NOT NULL, "order" INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494ECE07E8FF ON question (questionnaire_id)');
        $this->addSql('CREATE TABLE questionnaire (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_response (id SERIAL NOT NULL, user_id INT NOT NULL, questionnaire_id INT NOT NULL, answers JSON NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DEF6EFFBA76ED395 ON user_response (user_id)');
        $this->addSql('CREATE INDEX IDX_DEF6EFFBCE07E8FF ON user_response (questionnaire_id)');
        $this->addSql('COMMENT ON COLUMN user_response.completed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE answer_option ADD CONSTRAINT FK_A87F3A171E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_response ADD CONSTRAINT FK_DEF6EFFBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_response ADD CONSTRAINT FK_DEF6EFFBCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE answer_option DROP CONSTRAINT FK_A87F3A171E27F6BF');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECE07E8FF');
        $this->addSql('ALTER TABLE user_response DROP CONSTRAINT FK_DEF6EFFBA76ED395');
        $this->addSql('ALTER TABLE user_response DROP CONSTRAINT FK_DEF6EFFBCE07E8FF');
        $this->addSql('DROP TABLE answer_option');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE user_response');
    }
}
