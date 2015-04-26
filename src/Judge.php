<?php

namespace Judge;

use Judge\Exception\NotAuthorizedException;
use Judge\Repository\ArrayRepository;
use Judge\Repository\Repository;

class Judge
{
    /**
     * @var \Judge\Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository = null)
    {
        $this->repository = $repository ?: new ArrayRepository();
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
     * Enforce a rule being granted before proceeding.
     *
     * If access is revoked, throw a NotAuthorizedException, halting script
     * execution unless it is caught
     *
     * @param $identity
     * @param $role
     * @param null $context
     * @throws Exception\NotAuthorizedException
     * @return bool
     */
    public function enforce($identity, $role, $context = null)
    {
        if (!$this->check($identity, $role, $context)) {
            throw new NotAuthorizedException();
        }

        return true;
    }

    /**
     * Fire a callable if the rule has access granted
     *
     * @param $identity
     * @param $rule
     * @param null $context
     * @param callable $callable A callable to execute if access is granted
     * @return bool|mixed False if they do not have access, else the return value of $callable
     */
    public function attempt($identity, $rule, $context = null, callable $callable)
    {
        return $this->check($identity, $rule, $context) ? call_user_func($callable) : false;
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
