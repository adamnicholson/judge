<?php

namespace Judge\Repository;

final class ArrayRepository implements Repository
{
    private $rules = [];
    private $roles = [];
    private $identities = [];

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function deleteRule($identity, $role, $context)
    {
        foreach ($this->rules as $i => $rule) {
            if ($rule[0] === $identity && $rule[1] === $role && $rule[2] === $context) {
                unset($this->rules[$i]);
            }
        }
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function saveRole($role, $parent)
    {
        $this->roles[$role] = $parent;
    }

    /**
     * @inheritdoc
     */
    public function getRoleParent($role)
    {
        if (isset($this->roles[$role])) {
            return $this->roles[$role];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function saveIdentity($identity, $parent = null)
    {
        $this->identities[$identity] = $parent;
    }

    /**
     * @inheritdoc
     */
    public function getIdentityParent($identity)
    {
        if (isset($this->identities[$identity])) {
            return $this->identities[$identity];
        }

        return null;
    }
}
