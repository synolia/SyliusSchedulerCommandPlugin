<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191102175551 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create scheduled_command';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE scheduled_command (
          id INT AUTO_INCREMENT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          command VARCHAR(255) NOT NULL, 
          arguments VARCHAR(255) DEFAULT NULL, 
          cronExpression VARCHAR(255) NOT NULL, 
          lastExecution DATE DEFAULT NULL, 
          lastReturnCode INT DEFAULT NULL, 
          logFile VARCHAR(255) DEFAULT NULL, 
          priority INT NOT NULL, 
          executeImmediately TINYINT(1) NOT NULL, 
          disabled TINYINT(1) NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE scheduled_command');
    }
}
