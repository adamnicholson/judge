<?php

namespace Judge\Repository;

use Judge\TestCase;

abstract class RepositoryIntegrationTestCase extends TestCase
{
    /**
     * @return Repository
     */
    abstract public function getRepository();

    public function testRuleSaveAndGet()
    {
        $repo = $this->getRepository();
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_GRANT);
        $repo->saveRule('adam', 'PRODUCTS', null, Repository::STATE_REVOKE);
        $this->assertEquals($repo->getRuleState('adam', 'ORDERS', null), Repository::STATE_GRANT);
        $this->assertEquals($repo->getRuleState('adam', 'PRODUCTS', null), Repository::STATE_REVOKE);
    }

    public function testRoleSaveGetAndRemove()
    {
        $repo = $this->getRepository();
        $repo->saveRole('ORDER_EDIT', 'ORDER');
        $repo->saveRole('PRODUCT', null);
        $this->assertEquals($repo->getRoleParent('ORDER_EDIT'), 'ORDER');
        $this->assertEquals($repo->getRoleParent('PRODUCT'), null);
        $this->assertEquals($repo->getRoles(), ['ORDER_EDIT', 'PRODUCT']);
        $repo->removeRole('PRODUCT');
        $this->assertEquals($repo->getRoles(), ['ORDER_EDIT']);
    }

    public function testIdentitySaveGetAndRemove()
    {
        $repo = $this->getRepository();
        $repo->saveIdentity('adam', 'admin');
        $repo->saveIdentity('cli', null);
        $this->assertEquals($repo->getIdentityParent('adam'), 'admin');
        $this->assertEquals($repo->getIdentityParent('cli'), null);
        $this->assertEquals($repo->getIdentities(), ['adam', 'cli']);
        $repo->removeIdentity('cli');
        $this->assertEquals($repo->getIdentities(), ['adam']);
    }
}