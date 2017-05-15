<?php

namespace Judge\Repository;

use Judge\Repository\PDO\TableCreator;
use PDO;

class PDORepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    private $pdo;
    /** @var  PDORepository */
    private $repo;

    public function setUp()
    {
        $this->pdo = new PDO('sqlite::memory:', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->repo = new PDORepository($this->pdo);

        (new TableCreator($this->repo))->createTables();
    }

    public function tearDown()
    {
        (new TableCreator($this->repo))->dropTables();
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repo;
    }
}
