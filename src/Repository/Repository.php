<?php

namespace Judge\Repository;

interface Repository
{
    const STATE_ALLOW = 'ALLOW';
    const STATE_DENY = 'DENY';

    /**
     * Save a rule
     *
     * @param $identity
     * @param $role
     * @param $context
     * @param string $state STATE_GRANT or STATE_REVOKE
     * @return void
     */
    public function saveRule($identity, $role, $context, $state);

    /**
     * @param $identity
     * @param $role
     * @param $context
     * @return string|null
     */
    public function getRuleState($identity, $role, $context);

    /**
     * @param $role
     * @param $parent
     * @return void
     */
    public function saveRole($role, $parent);

    /**
     * @param $role
     * @return void
     */
    public function removeRole($role);

    /**
     * @param $role
     * @return array|null
     */
    public function getRoleParent($role);

    /**
     * @return array
     */
    public function getRoles();

    /**
     * @param string $identity
     * @param string $parent
     * @return void
     */
    public function saveIdentity($identity, $parent = null);

    /**
     * @param string $identity
     * @return void
     */
    public function removeIdentity($identity);

    /**
     * @param $identity
     * @return string|null
     */
    public function getIdentityParent($identity);

    /**
     * Get all of the saved identities
     *
     * @return array
     */
    public function getIdentities();
}
