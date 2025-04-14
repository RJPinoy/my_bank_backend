<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121125937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transactions ADD category_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4C12469DE2 ON transactions (category_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4CA76ED395 ON transactions (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C12469DE2');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CA76ED395');
        $this->addSql('DROP INDEX IDX_EAA81A4C12469DE2 ON transactions');
        $this->addSql('DROP INDEX IDX_EAA81A4CA76ED395 ON transactions');
        $this->addSql('ALTER TABLE transactions DROP category_id, DROP user_id');
    }
}
