<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019112528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY animal_ibfk_2');
        $this->addSql('ALTER TABLE animal CHANGE habitat_id habitat_id INT NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE state state LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231FAFFE2D26 FOREIGN KEY (habitat_id) REFERENCES habitat (id)');
        $this->addSql('ALTER TABLE animal RENAME INDEX name TO UNIQ_6AAB231F5E237E06');
        $this->addSql('ALTER TABLE animal RENAME INDEX race_id TO IDX_6AAB231F6E59D40D');
        $this->addSql('ALTER TABLE animal RENAME INDEX habitat_id TO IDX_6AAB231FAFFE2D26');
        $this->addSql('ALTER TABLE animal_image ADD animal_id INT NOT NULL, CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE animal_image ADD CONSTRAINT FK_E4CEDDAB8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id)');
        $this->addSql('CREATE INDEX IDX_E4CEDDAB8E962C16 ON animal_image (animal_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B37B2E85E237E06 ON habitat (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rapport (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, detail TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, user_id INT NOT NULL, animal_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231FAFFE2D26');
        $this->addSql('ALTER TABLE animal CHANGE habitat_id habitat_id INT DEFAULT NULL, CHANGE name name VARCHAR(50) NOT NULL, CHANGE state state VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT animal_ibfk_2 FOREIGN KEY (habitat_id) REFERENCES habitat (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal RENAME INDEX idx_6aab231f6e59d40d TO race_id');
        $this->addSql('ALTER TABLE animal RENAME INDEX idx_6aab231faffe2d26 TO habitat_id');
        $this->addSql('ALTER TABLE animal RENAME INDEX uniq_6aab231f5e237e06 TO name');
        $this->addSql('ALTER TABLE animal_image DROP FOREIGN KEY FK_E4CEDDAB8E962C16');
        $this->addSql('DROP INDEX IDX_E4CEDDAB8E962C16 ON animal_image');
        $this->addSql('ALTER TABLE animal_image DROP animal_id, CHANGE path path VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_3B37B2E85E237E06 ON habitat');
    }
}
