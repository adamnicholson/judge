<?php

namespace Judge\Repository;

class PDORepository implements Repository
{
    private $identityTable = 'judge_identity';
    private $roleTable = 'judge_role';
    private $ruleTable = 'judge_rule';

    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Save a rule
     *
     * @param $identity
     * @param $role
     * @param $context
     * @param string $state Repository::STATE_GRANT or Repository::STATE_REVOKE
     * @return void
     */
    public function saveRule($identity, $role, $context, $state)
    {
        $this->query("INSERT INTO " . $this->ruleTable . " (`identity`, `role`, `context`, `state`) VALUES (?, ?, ?, ?)", [
                $identity,
                $role,
                $context ?: '',
                $state
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
        $rule = $this->query("SELECT * FROM " . $this->ruleTable . " WHERE `identity` = ? AND `role` = ? AND `context` = ?",[
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
        $this->query("INSERT INTO " . $this->roleTable . " (`name`, `parent`) VALUES (?, ?)", [$role, $parent]);
    }

    /**
     * @param $role
     * @return void
     */
    public function removeRole($role)
    {
        $this->query("DELETE FROM " . $this->roleTable . " WHERE `name` = ?", [$role]);
    }

    /**
     * @param $role
     * @return array|null
     */
    public function getRoleParent($role)
    {
        $role = $this->query("SELECT * FROM " . $this->roleTable . " WHERE `name` = ?", [$role])->fetchObject();

        return $role ? $role->parent : null;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $query = $this->query("SELECT * FROM " . $this->roleTable)->fetchAll();

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
        $this->query("INSERT INTO " . $this->identityTable . " (`name`, `parent`) VALUES (?, ?)", [$identity, $parent]);
    }

    /**
     * @param string $identity
     * @return void
     */
    public function removeIdentity($identity)
    {
        $this->query("DELETE FROM " . $this->identityTable . " WHERE `name` = ?", [$identity]);
    }

    /**
     * @param $identity
     * @return string|null
     */
    public function getIdentityParent($identity)
    {
        $identity = $this->query("SELECT * FROM " . $this->identityTable . " WHERE `name` = ?", [$identity])->fetchObject();

        return $identity ? $identity->parent : null;
    }

    /**
     * Get all of the saved identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $query = $this->query("SELECT * FROM " . $this->identityTable)->fetchAll();

        return array_map(function ($row) {
            return $row['name'];
        }, $query);
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
