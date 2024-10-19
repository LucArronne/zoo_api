<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019173247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_rapport (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, animal_id INT NOT NULL, state LONGTEXT NOT NULL, food VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', details LONGTEXT DEFAULT NULL, INDEX IDX_31EBCFA6A76ED395 (user_id), INDEX IDX_31EBCFA68E962C16 (animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA68E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id)');
        $this->addSql('DROP INDEX UNIQ_6AAB231F5E237E06 ON animal');
        $this->addSql('ALTER TABLE animal DROP state');
        $this->addSql('ALTER TABLE animal_image CHANGE path path LONGTEXT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_3B37B2E85E237E06 ON habitat');
        $this->addSql('ALTER TABLE habitat CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE habitat_image CHANGE path path LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA6A76ED395');
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA68E962C16');
        $this->addSql('DROP TABLE animal_rapport');
        $this->addSql('ALTER TABLE animal ADD state LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AAB231F5E237E06 ON animal (name)');
        $this->addSql('ALTER TABLE habitat_image CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE animal_image CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE habitat CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B37B2E85E237E06 ON habitat (name)');
    }
}
