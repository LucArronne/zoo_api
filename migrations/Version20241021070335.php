<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021070335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_image CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E4CEDDABB548B0F ON animal_image (path)');
        $this->addSql('ALTER TABLE habitat_image CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9AD7E031B548B0F ON habitat_image (path)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_9AD7E031B548B0F ON habitat_image');
        $this->addSql('ALTER TABLE habitat_image CHANGE path path LONGTEXT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_E4CEDDABB548B0F ON animal_image');
        $this->addSql('ALTER TABLE animal_image CHANGE path path LONGTEXT NOT NULL');
    }
}
