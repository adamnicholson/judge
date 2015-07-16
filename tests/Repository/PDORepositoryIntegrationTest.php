<?php

namespace Judge\Repository;

use PDO;

class PDORepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    private $pdo;

    public function setUp()
    {
        $this->pdo = new PDO('sqlite::memory', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE `judge_identity`(
                `name` VARCHAR(255),
                `parent` VARCHAR(255),
                primary KEY (`name`)
            );
        ");
        $this->pdo->exec("
            CREATE TABLE judge_role (
                `name` VARCHAR(255),
                `parent` VARCHAR(255),
                primary KEY (`name`)
            );
        ");
        $this->pdo->exec("
            CREATE TABLE judge_rule (
                `identity` VARCHAR(255),
                `role` VARCHAR(255),
                `context` VARCHAR(255),
                `state` VARCHAR(255),
                primary KEY (`identity`, `role`, `context`)
            );
        ");
    }

    public function tearDown()
    {
        $this->pdo->exec("DROP TABLE judge_identity");
        $this->pdo->exec("DROP TABLE judge_role");
        $this->pdo->exec("DROP TABLE judge_rule");
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return new PDORepository($this->pdo);
    }
}
