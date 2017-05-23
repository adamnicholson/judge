<?php

namespace Judge\Repository;

interface Repository
{
    const STATE_ALLOW = 'ALLOW';
    const STATE_DENY = 'DENY';

    /**
     * Save a rule
     *
     * @param string $identity
     * @param string $role
     * @param string $context
     * @param string $state STATE_ALLOW or STATE_DENY
     * @return void
     */
    public function saveRule($identity, $role, $context, $state);

    /**
     * Delete an existing rule
     *
     * @param string $identity
     * @param string $role
     * @param string $context
     * @return void
     */
    public function deleteRule($identity, $role, $context);

    /**
     * @param string $identity
     * @param string $role
     * @param string $context
     * @return string|null
     */
    public function getRuleState($identity, $role, $context);

    /**
     * @param string $role
     * @param string $parent
     * @return void
     */
    public function saveRole($role, $parent);

    /**
     * @param string $role
     * @return string|null
     */
    public function getRoleParent($role);

    /**
     * @param string $identity
     * @param string $parent
     * @return void
     */
    public function saveIdentity($identity, $parent = null);

    /**
     * @param string $identity
     * @return string|null
     */
    public function getIdentityParent($identity);
}
