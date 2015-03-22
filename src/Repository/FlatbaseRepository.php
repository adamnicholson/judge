<?php

namespace Judge\Repository;

use Flatbase\Flatbase;

class FlatbaseRepository implements Repository
{
    /**
     * @var \Flatbase\Flatbase
     */
    private $flatbase;
    /**
     * @var string
     */
    private $ruleCollectionName;
    /**
     * @var string
     */
    private $roleCollectionName;
    /**
     * @var string
     */
    private $identityCollectionName;

    /**
     * @param Flatbase $flatbase
     * @param string $ruleCollectionName
     * @param string $roleCollectionName
     * @param string $identityCollectionName
     */
    public function __construct(
        Flatbase $flatbase,
        $ruleCollectionName = 'rules',
        $roleCollectionName = 'roles',
        $identityCollectionName = 'identities'
    ) {
        $this->flatbase = $flatbase;
        $this->ruleCollectionName = $ruleCollectionName;
        $this->roleCollectionName = $roleCollectionName;
        $this->identityCollectionName = $identityCollectionName;
    }

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
        $this->flatbase->delete()->in($this->ruleCollectionName)
            ->where('identity', '==', $identity)
            ->where('role', '==', $role)
            ->where('context', '==', $context)
            ->execute();

        $this->flatbase->insert()->in($this->ruleCollectionName)->setValues([
                'identity' => $identity,
                'role' => $role,
                'context' => $context,
                'state' => $state
        ])->execute();
    }

    /**
     * @param $identity
     * @param $role
     * @param $context
     * @return string|null
     */
    public function getRuleState($identity, $role, $context)
    {
        $rule = $this->flatbase->read()->in($this->ruleCollectionName)
            ->where('identity', '==', $identity)
            ->where('role', '==', $role)
            ->where('context', '==', $context)
            ->first();

        if (!$rule) {
            return null;
        }

        return $rule['state'];
    }

    /**
     * @param $role
     * @param $parent
     * @return void
     */
    public function saveRole($role, $parent)
    {
        $this->removeRole($role);

        $this->flatbase->insert()->in($this->roleCollectionName)->setValues([
                'role' => $role,
                'parent' => $parent,
            ])->execute();
    }

    /**
     * @param $role
     * @return void
     */
    public function removeRole($role)
    {
        $this->flatbase->delete()->in($this->roleCollectionName)
            ->where('role', '==', $role)
            ->execute();
    }

    /**
     * @param $role
     * @return array|null
     */
    public function getRoleParent($role)
    {
        $role = $this->flatbase->read()->in($this->roleCollectionName)
            ->where('role', '==', $role)
            ->first();

        if (!$role) {
            return null;
        }

        return $role['parent'];
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = [];

        foreach ($this->flatbase->read()->in($this->roleCollectionName)->get() as $role) {
            $roles[] = $role['role'];
        }

        return $roles;
    }

    /**
     * @param string $identity
     * @param string $parent
     * @return void
     */
    public function saveIdentity($identity, $parent = null)
    {
        $this->removeIdentity($identity);

        $this->flatbase->insert()->in($this->identityCollectionName)->setValues([
            'identity' => $identity,
            'parent' => $parent
        ])->execute();
    }

    /**
     * @param string $identity
     * @return void
     */
    public function removeIdentity($identity)
    {
        $this->flatbase->delete()->in($this->identityCollectionName)
            ->where('identity', '==', $identity)
            ->execute();
    }

    /**
     * @param $identity
     * @return string|null
     */
    public function getIdentityParent($identity)
    {
        $identity = $this->flatbase->read()->in($this->identityCollectionName)
            ->where('identity', '==', $identity)
            ->first();

        if (!$identity) {
            return null;
        }

        return $identity['parent'];
    }

    /**
     * Get all of the saved identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];

        foreach ($this->flatbase->read()->in($this->identityCollectionName)->get() as $identity) {
            $identities[] = $identity['identity'];
        }

        return $identities;
    }

}
