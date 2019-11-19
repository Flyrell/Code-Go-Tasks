<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181219111051 extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @throws DBALException|AbortMigrationException
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(128) NOT NULL, last_name VARCHAR(128) NOT NULL, birthday DATETIME DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, employee_id INT DEFAULT NULL, primary_skill_id INT DEFAULT NULL, month DATETIME NOT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_6D28840D8C03F15C (employee_id), INDEX IDX_6D28840D74FAACB3 (primary_skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, level SMALLINT NOT NULL, min_salary NUMERIC(10, 2) NOT NULL, max_salary NUMERIC(10, 2) NOT NULL, avg_salary NUMERIC(10, 2) NOT NULL, median_salary NUMERIC(10, 2) NOT NULL, UNIQUE INDEX name_level (name, level), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employees_skills (skill_id INT NOT NULL, employee_id INT NOT NULL, INDEX IDX_297D0E55585C142 (skill_id), INDEX IDX_297D0E58C03F15C (employee_id), PRIMARY KEY(skill_id, employee_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, employee_id INT DEFAULT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, postal_code VARCHAR(32) NOT NULL, country VARCHAR(32) NOT NULL, INDEX IDX_D4E6F818C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D74FAACB3 FOREIGN KEY (primary_skill_id) REFERENCES skill (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE employees_skills ADD CONSTRAINT FK_297D0E55585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE employees_skills ADD CONSTRAINT FK_297D0E58C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F818C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id)');
    }

    /**
     * @param Schema $schema
     *
     * @throws DBALException|AbortMigrationException
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D8C03F15C');
        $this->addSql('ALTER TABLE employees_skills DROP FOREIGN KEY FK_297D0E58C03F15C');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F818C03F15C');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D74FAACB3');
        $this->addSql('ALTER TABLE employees_skills DROP FOREIGN KEY FK_297D0E55585C142');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE employees_skills');
        $this->addSql('DROP TABLE address');
    }
}
