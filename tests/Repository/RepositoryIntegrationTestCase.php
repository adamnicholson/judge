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
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_ALLOW);
        $repo->saveRule('adam', 'PRODUCTS', null, Repository::STATE_DENY);
        $this->assertEquals($repo->getRuleState('adam', 'ORDERS', null), Repository::STATE_ALLOW);
        $this->assertEquals($repo->getRuleState('adam', 'PRODUCTS', null), Repository::STATE_DENY);
    }

    public function test_roles_can_be_saved_and_updated()
    {
        $repo = $this->getRepository();
        $repo->saveRole('ORDER_EDIT', 'ORDER');
        $repo->saveRole('PRODUCT', null);
        $this->assertEquals($repo->getRoleParent('ORDER_EDIT'), 'ORDER');
        $this->assertEquals($repo->getRoleParent('PRODUCT'), null);
        $repo->saveRole('ORDER_EDIT', 'ALL_ROLES');
        $this->assertEquals($repo->getRoleParent('ORDER_EDIT'), 'ALL_ROLES');
    }

    public function test_identities_can_be_saved_and_updated()
    {
        $repo = $this->getRepository();
        $repo->saveIdentity('adam', 'admin');
        $repo->saveIdentity('cli', null);
        $this->assertEquals($repo->getIdentityParent('adam'), 'admin');
        $this->assertEquals($repo->getIdentityParent('cli'), null);
        $repo->saveIdentity('adam', 'staff');
        $this->assertEquals($repo->getIdentityParent('adam'), 'staff');
    }

    public function test_rule_saves_can_be_overridden()
    {
        $repo = $this->getRepository();
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_ALLOW);
        $this->assertEquals(Repository::STATE_ALLOW, $repo->getRuleState('adam', 'ORDERS', null));
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_ALLOW);
        $this->assertEquals(Repository::STATE_ALLOW, $repo->getRuleState('adam', 'ORDERS', null));

        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_DENY);
        $this->assertEquals(Repository::STATE_DENY, $repo->getRuleState('adam', 'ORDERS', null));
    }

    public function test_identities_can_be_updated()
    {
        $repo = $this->getRepository();
        $repo->saveIdentity('adam', 'management');
        $this->assertEquals('management', $repo->getIdentityParent('adam'));
        $repo->saveIdentity('adam', 'staff');
        $this->assertEquals('staff', $repo->getIdentityParent('adam'));
    }

    public function test_saved_rules_can_be_deleted()
    {
        $repo = $this->getRepository();
        $this->assertEquals(null, $repo->getRuleState('adam', 'WRITE', 'foostate'));
        $repo->saveRule('adam', 'READ', null, Repository::STATE_ALLOW);
        $repo->saveRule('adam', 'WRITE', null, Repository::STATE_DENY);
        $repo->saveRule('adam', 'WRITE', 'foostate', Repository::STATE_ALLOW);
        $repo->saveRule('adam', 'WRITE', 'barstate', Repository::STATE_DENY);

        $this->assertEquals(Repository::STATE_ALLOW, $repo->getRuleState('adam', 'WRITE', 'foostate'));
        $this->assertEquals(Repository::STATE_DENY, $repo->getRuleState('adam', 'WRITE', 'barstate'));
        $this->assertEquals(Repository::STATE_DENY, $repo->getRuleState('adam', 'WRITE', null));
        $this->assertEquals(Repository::STATE_ALLOW, $repo->getRuleState('adam', 'READ', null));

        $repo->deleteRule('adam', 'WRITE', 'foostate');

        $this->assertEquals(null, $repo->getRuleState('adam', 'WRITE', 'foostate'));
        $this->assertEquals(Repository::STATE_DENY, $repo->getRuleState('adam', 'WRITE', 'barstate'));
        $this->assertEquals(Repository::STATE_DENY, $repo->getRuleState('adam', 'WRITE', null));
        $this->assertEquals(Repository::STATE_ALLOW, $repo->getRuleState('adam', 'READ', null));
    }
}
