<?php

namespace Judge\Repository;

class PDORepository implements Repository
{
    private $identityTableName = 'judge_identity';
    private $roleTableName = 'judge_role';
    private $ruleTableName = 'judge_rule';

    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritdoc
     */
    public function saveRule($identity, $role, $context, $state)
    {
        $context = $context ?: '';

        $this->upsert($this->ruleTableName, [
            ['identity', $identity],
            ['role', $role],
            ['context', $context],
            ['state', $state]
        ], [
            ['identity', $identity],
            ['role', $role],
            ['context', $context],
        ]);
    }

    /**
     * @param $identity
     * @param $role
     * @param $context
     * @return string|null
     */
    public function getRuleState($identity, $role, $context)
    {
        $rule = $this->query("SELECT * FROM " . $this->ruleTableName . " WHERE `identity` = ? AND `role` = ? AND `context` = ?",[
                $identity,
                $role,
                $context ?: ''
            ])->fetchObject();

        return $rule ? $rule->state : null;
    }

    /**
     * @param $role
     * @param $parent
     * @return void
     */
    public function saveRole($role, $parent)
    {
        $this->removeRole($role);
        $this->query("INSERT INTO " . $this->roleTableName . " (`name`, `parent`) VALUES (?, ?)", [$role, $parent]);
    }

    /**
     * @param $role
     * @return void
     */
    public function removeRole($role)
    {
        $this->query("DELETE FROM " . $this->roleTableName . " WHERE `name` = ?", [$role]);
    }

    /**
     * @param $role
     * @return array|null
     */
    public function getRoleParent($role)
    {
        $role = $this->query("SELECT * FROM " . $this->roleTableName . " WHERE `name` = ?", [$role])->fetchObject();

        return $role ? $role->parent : null;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $query = $this->query("SELECT * FROM " . $this->roleTableName)->fetchAll();

        return array_map(function ($row) {
                return $row['name'];
            }, $query);
    }

    /**
     * @param string $identity
     * @param string $parent
     * @return void
     */
    public function saveIdentity($identity, $parent = null)
    {
        $this->upsert($this->identityTableName, [
            ['name', $identity],
            ['parent', $parent],
        ], [
            ['name', $identity],
        ]);
    }

    /**
     * @param $identity
     * @return string|null
     */
    public function getIdentityParent($identity)
    {
        $identity = $this->query("SELECT * FROM " . $this->identityTableName . " WHERE `name` = ?", [$identity])->fetchObject();

        return $identity ? $identity->parent : null;
    }

    /**
     * Get all of the saved identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $query = $this->query("SELECT * FROM " . $this->identityTableName)->fetchAll();

        return array_map(function ($row) {
            return $row['name'];
        }, $query);
    }

    /**
     * @return string
     */
    public function getRuleTableName(): string
    {
        return $this->ruleTableName;
    }

    /**
     * @return string
     */
    public function getRoleTableName(): string
    {
        return $this->roleTableName;
    }

    /**
     * @return string
     */
    public function getIdentityTableName(): string
    {
        return $this->identityTableName;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $table
     * @param array $params
     * @param array $matchOn
     */
    public function upsert(string $table, array $params, array $matchOn)
    {
        $exists = $this->select($table, $matchOn)->fetchObject();

        if (!$exists) {
            $this->insert($table, $params);
        } else {
            $this->update($table, $params, $matchOn);
        }
    }

    /**
     * @param string $table
     * @param array $params
     */
    private function insert(string $table, array $params)
    {
        $sql = "INSERT INTO {$table} (" . implode(',', array_map('array_shift', $params))  . ') VALUES (' . implode(',', array_pad([], count($params), '?')) . ')';

        $this->query($sql, array_values(array_map('end', $params)));
    }

    /**
     * @param string $table
     * @param array $params
     * @param array $matchOn
     */
    private function update(string $table, array $params, array $matchOn)
    {
        $sql = "UPDATE {$table} SET " . implode(' , ', array_map(function (array $element) {
                return "`{$element[0]}` = ?";
            }, $params)) . " WHERE " . implode(' AND ', array_map(function (array $element) {
                return "`{$element[0]}` = ?";
            }, $matchOn));

        $this->query($sql, array_merge(array_map('end', $params), array_map('end', $matchOn)));
    }

    /**
     * @param string $table
     * @param array $matchOn
     * @return \PDOStatement
     */
    private function select(string $table, array $matchOn)
    {
        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', array_map(function (array $element) {
                return "`{$element[0]}` = ?";
            }, $matchOn));

        return $this->query($sql, array_map('end', $matchOn));
    }

    /**
     * @param $statement
     * @param array $arguments
     * @return \PDOStatement
     */
    private function query($statement, $arguments = [])
    {
        $query = $this->pdo->prepare($statement);
        $query->execute($arguments);

        return $query;
    }
}
