<?php

namespace Judge\Repository\PDO;

use Judge\Repository\PDORepository;

final class TableCreator
{
    /**
     * @var PDORepository
     */
    private $repository;

    /**
     * TableCreator constructor.
     * @param PDORepository $repository
     */
    public function __construct(PDORepository $repository)
    {
        $this->repository = $repository;
        $this->pdo = $this->repository->getPdo();
    }

    public function createTables()
    {
        $this->pdo->exec("
            CREATE TABLE `{$this->repository->getIdentityTableName()}`(
                `name` VARCHAR(255),
                `parent` VARCHAR(255),
                primary KEY (`name`)
            );
        ");
        $this->pdo->exec("
            CREATE TABLE `{$this->repository->getRoleTableName()}` (
                `name` VARCHAR(255),
                `parent` VARCHAR(255),
                primary KEY (`name`)
            );
        ");
        $this->pdo->exec("
            CREATE TABLE `{$this->repository->getRuleTableName()}` (
                `identity` VARCHAR(255),
                `role` VARCHAR(255),
                `context` VARCHAR(255),
                `state` VARCHAR(255),
                primary KEY (`identity`, `role`, `context`)
            );
        ");
    }

    public function dropTables()
    {
        $this->pdo->exec("DROP TABLE `{$this->repository->getIdentityTableName()}`");
        $this->pdo->exec("DROP TABLE `{$this->repository->getRoleTableName()}`");
        $this->pdo->exec("DROP TABLE `{$this->repository->getRuleTableName()}`");
    }
}
