<?php

namespace Judge;

use Judge\Repository\Repository;

class Judge
{
    /**
     * @var Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $identity
     * @param string $role
     * @param string|null $context
     * @return bool
     */
    public function check($identity, $role, $context = null)
    {
        // Work up the identity tree
        $innerIdentity = $identity;

        while ($innerIdentity) {
            $state = $this->getRepository()->getRuleState($innerIdentity, $role, $context);

            if ($state) {
                return $state === Repository::STATE_GRANT;
            }

            $innerIdentity = $this->getRepository()->getIdentityParent($innerIdentity);
        }

        // Check this role without the context
        if ($context) {
            return $this->check($identity, $role);
        }

        // Work up the role tree
        if ($parentRole = $this->getRepository()->getRoleParent($role)) {
            return $this->check($identity, $parentRole);
        }

        return false;
    }

    /**
     * @param string $identity
     * @param string $role
     * @param string|null $context
     */
    public function grant($identity, $role, $context = null)
    {
        $this->repository->saveRule($identity, $role, $context, Repository::STATE_GRANT);
    }

    /**
     * @param string $identity
     * @param string $role
     * @param string|null $context
     */
    public function revoke($identity, $role, $context = null)
    {
        $this->repository->saveRule($identity, $role, $context, Repository::STATE_REVOKE);
    }

    /**
     * @return Repository|Repository\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}