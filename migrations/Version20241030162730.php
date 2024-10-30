<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030162730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id INT AUTO_INCREMENT NOT NULL, race_id INT NOT NULL, habitat_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_6AAB231F6E59D40D (race_id), INDEX IDX_6AAB231FAFFE2D26 (habitat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animal_food (id INT AUTO_INCREMENT NOT NULL, animal_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_931568C38E962C16 (animal_id), INDEX IDX_931568C3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animal_image (id INT AUTO_INCREMENT NOT NULL, animal_id INT NOT NULL, path VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E4CEDDABB548B0F (path), INDEX IDX_E4CEDDAB8E962C16 (animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animal_rapport (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, animal_id INT DEFAULT NULL, state LONGTEXT NOT NULL, food VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', details LONGTEXT DEFAULT NULL, INDEX IDX_31EBCFA6A76ED395 (user_id), INDEX IDX_31EBCFA68E962C16 (animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(50) NOT NULL, text LONGTEXT NOT NULL, is_visible TINYINT(1) DEFAULT NULL, created_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE habitat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_3B37B2E85E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE habitat_habitat_image (habitat_id INT NOT NULL, habitat_image_id INT NOT NULL, INDEX IDX_A5C5B042AFFE2D26 (habitat_id), INDEX IDX_A5C5B042521FE96 (habitat_image_id), PRIMARY KEY(habitat_id, habitat_image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE habitat_image (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9AD7E031B548B0F (path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE race (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_DA6FBBAF5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, value VARCHAR(50) NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_E19D9AD25E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_8D93D649D60322AC (role_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F6E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231FAFFE2D26 FOREIGN KEY (habitat_id) REFERENCES habitat (id)');
        $this->addSql('ALTER TABLE animal_food ADD CONSTRAINT FK_931568C38E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal_food ADD CONSTRAINT FK_931568C3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal_image ADD CONSTRAINT FK_E4CEDDAB8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id)');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal_rapport ADD CONSTRAINT FK_31EBCFA68E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE habitat_habitat_image ADD CONSTRAINT FK_A5C5B042AFFE2D26 FOREIGN KEY (habitat_id) REFERENCES habitat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE habitat_habitat_image ADD CONSTRAINT FK_A5C5B042521FE96 FOREIGN KEY (habitat_image_id) REFERENCES habitat_image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F6E59D40D');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231FAFFE2D26');
        $this->addSql('ALTER TABLE animal_food DROP FOREIGN KEY FK_931568C38E962C16');
        $this->addSql('ALTER TABLE animal_food DROP FOREIGN KEY FK_931568C3A76ED395');
        $this->addSql('ALTER TABLE animal_image DROP FOREIGN KEY FK_E4CEDDAB8E962C16');
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA6A76ED395');
        $this->addSql('ALTER TABLE animal_rapport DROP FOREIGN KEY FK_31EBCFA68E962C16');
        $this->addSql('ALTER TABLE habitat_habitat_image DROP FOREIGN KEY FK_A5C5B042AFFE2D26');
        $this->addSql('ALTER TABLE habitat_habitat_image DROP FOREIGN KEY FK_A5C5B042521FE96');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE animal_food');
        $this->addSql('DROP TABLE animal_image');
        $this->addSql('DROP TABLE animal_rapport');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE habitat');
        $this->addSql('DROP TABLE habitat_habitat_image');
        $this->addSql('DROP TABLE habitat_image');
        $this->addSql('DROP TABLE race');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE user');
    }
}
