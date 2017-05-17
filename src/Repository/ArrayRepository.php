<?php

namespace Judge\Repository;

final class ArrayRepository implements Repository
{
    private $rules = [];
    private $roles = [];
    private $identities = [];

    /**
     * Save a rule
     *
     * @param $identity
     * @param $role
     * @param $context
     * @param string $state STATE_GRANT or STATE_REVOKE
     * @return void
     */
    public function saveRule($identity, $role, $context, $state)
    {
        $this->rules["$identity:$role:$context"] = [
            $identity,
            $role,
            $context,
            $state
        ];
    }

    /**
     * @param $identity
     * @param $role
     * @param $context
     * @return string|null
     */
    public function getRuleState($identity, $role, $context)
    {
        foreach ($this->rules as $rule) {
            if ($rule[0] === $identity && $rule[1] === $role && $rule[2] === $context) {
                return $rule[3];
            }
        }

        return null;
    }

    /**
     * @param $role
     * @param $parent
     * @return void
     */
    public function saveRole($role, $parent)
    {
        $this->roles[$role] = $parent;
    }

    /**
     * @param $role
     * @return void
     */
    public function removeRole($role)
    {
        unset($this->roles[$role]);
    }

    /**
     * @param $role
     * @return array|null
     */
    public function getRoleParent($role)
    {
        if (isset($this->roles[$role])) {
            return $this->roles[$role];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return array_keys($this->roles);
    }

    /**
     * @param string $identity
     * @param string $parent
     * @return void
     */
    public function saveIdentity($identity, $parent = null)
    {
        $this->identities[$identity] = $parent;
    }

    /**
     * @param string $identity
     * @return void
     */
    public function removeIdentity($identity)
    {
        unset($this->identities[$identity]);
    }

    /**
     * @param $identity
     * @return string|null
     */
    public function getIdentityParent($identity)
    {
        if (isset($this->identities[$identity])) {
            return $this->identities[$identity];
        }

        return null;
    }

    /**
     * Get all of the saved identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array_keys($this->identities);
    }
}
