<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023195701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA68E962C16');
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA6A76ED395');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA68E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA6A76ED395');
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA68E962C16');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA68E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id)');
    }
}
