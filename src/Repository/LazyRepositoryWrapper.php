<?php

namespace Judge\Repository;

use UnexpectedValueException;

final class LazyRepositoryWrapper implements \Judge\Repository\Repository
{
    /**
     * @var callable
     */
    private $factory;
    /**
     * @var \Judge\Repository\Repository|null
     */
    private $repository = null;

    /**
     * LazyRepositoryWrapper constructor.
     * @param callable $factory
     *  A callable which will return an instance of Repository when called.
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function saveRule($identity, $role, $context, $state)
    {
        $this->inner()->saveRule($identity, $role, $context, $state);
    }

    public function getRuleState($identity, $role, $context)
    {
        return $this->inner()->getRuleState($identity, $role, $context);
    }

    public function saveRole($role, $parent)
    {
        $this->inner()->saveRole($role, $parent);
    }

    public function getRoleParent($role)
    {
        return $this->inner()->getRoleParent($role);
    }

    public function getRoles()
    {
        return $this->inner()->getRoles();
    }

    public function saveIdentity($identity, $parent = null)
    {
        $this->inner()->saveIdentity($identity, $parent);
    }

    public function getIdentityParent($identity)
    {
        return $this->inner()->getIdentityParent($identity);
    }

    /**
     * @return \Judge\Repository\Repository
     * @throws \UnexpectedValueException
     */
    private function inner()
    {
//        dump("inner");
        if ($this->repository === null) {
//            dump("making");
            $this->repository = call_user_func($this->factory);
            if (!$this->repository instanceof \Judge\Repository\Repository) {
                throw new UnexpectedValueException(
                    "Return value of callback passed to " . get_class($this) .
                    " MUST be an instance of " . \Judge\Repository\Repository::class
                );
            }
        }

//        dump($this->repository);

        return $this->repository;
    }
}
