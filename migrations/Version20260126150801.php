<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260126150801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membership DROP CONSTRAINT fk_86ffd285fb88e14f');
        $this->addSql('DROP INDEX idx_86ffd285fb88e14f');
        $this->addSql('ALTER TABLE membership RENAME COLUMN user_id TO utilisateur_id');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_86FFD285FB88E14F ON membership (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membership DROP CONSTRAINT FK_86FFD285FB88E14F');
        $this->addSql('DROP INDEX IDX_86FFD285FB88E14F');
        $this->addSql('ALTER TABLE membership RENAME COLUMN utilisateur_id TO user_id');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT fk_86ffd285fb88e14f FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_86ffd285fb88e14f ON membership (user_id)');
    }
}
