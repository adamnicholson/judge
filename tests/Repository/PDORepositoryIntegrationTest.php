<?php

namespace Judge\Repository;

class PDORepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        $pdo = new \PDO('mysql:host=localhost;dbname=judge_test', 'root');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec("DELETE FROM judge_identity");
        $pdo->exec("DELETE FROM judge_role");
        $pdo->exec("DELETE FROM judge_rule");
        return new PDORepository($pdo);
    }
}
