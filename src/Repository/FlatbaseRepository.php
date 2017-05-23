<?php

namespace Judge\Repository;

use Flatbase\Flatbase;

final class FlatbaseRepository implements Repository
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
     * @inheritdoc
     */
    public function saveRule($identity, $role, $context, $state)
    {
        $this->deleteRule($identity, $role, $context);

        $this->flatbase->insert()->in($this->ruleCollectionName)->setValues([
                'identity' => $identity,
                'role' => $role,
                'context' => $context,
                'state' => $state
        ])->execute();
    }

    /**
     * @inheritdoc
     */
    public function deleteRule($identity, $role, $context)
    {
        $this->flatbase->delete()->in($this->ruleCollectionName)
            ->where('identity', '==', $identity)
            ->where('role', '==', $role)
            ->where('context', '==', $context)
            ->execute();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
    private function removeRole($role)
    {
        $this->flatbase->delete()->in($this->roleCollectionName)
            ->where('role', '==', $role)
            ->execute();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
    private function removeIdentity($identity)
    {
        $this->flatbase->delete()->in($this->identityCollectionName)
            ->where('identity', '==', $identity)
            ->execute();
    }

    /**
     * @inheritdoc
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
