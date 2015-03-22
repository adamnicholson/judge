<?php

namespace Judge;

use Judge\Repository\Repository;

class JudgeTest extends TestCase
{
    public function testInstance()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $this->assertInstanceOf('Judge\Judge', new Judge($repo->reveal()));
    }

    public function testGrantUpdatesRepository()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_GRANT)->shouldBeCalled();
        $judge->grant('adam', 'ORDERS');
    }

    public function testRevokeUpdatesRepository()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_REVOKE)->shouldBeCalled();
        $judge->revoke('adam', 'ORDERS');
    }

    public function testGetRepositoryReturnsRepo()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());
        $this->assertEquals($judge->getRepository(), $repo->reveal());
    }

    public function testCheckWithSingleLevelIdentityTreeReturnsTrueWhenGranted()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_GRANT);

        $this->assertTrue($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithSingleLevelIdentityTreeReturnsFalseWhenRevoked()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_REVOKE);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithSingleLevelIdentityTreeReturnsFalseWhenNotSet()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelIdentityTreeReturnsTrueWhenRuleNotSetForUserButGrantedForParent()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('administrator');
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('administrator', 'ORDERS', null)->willReturn(Repository::STATE_GRANT);

        $this->assertTrue($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelIdentityTreeReturnsFalseWhenRuleNotSetForUserButRevokedForParent()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('administrator');
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('administrator', 'ORDERS', null)->willReturn(Repository::STATE_REVOKE);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelIdentityTreeReturnsFalseWhenRuleNotSetForIdentityOrParents()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('administrator');
        $repo->getIdentityParent('administrator')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('administrator', 'ORDERS', null)->willReturn(null);
        $repo->getRoleParent('ORDERS')->willReturn(null);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelRoleTreeReturnsTrueWhenNotSetForRoleButGrantedForParent()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_GRANT);

        $this->assertTrue($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckWithMultiLevelRoleTreeReturnsFalseWhenNotSetForRoleButRevokedForParent()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_REVOKE);

        $this->assertFalse($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckWithMultiLevelRoleTreeReturnsFalseWhenNotSetForRoleOrParentRoles()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);

        $this->assertFalse($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckReturnsFalseWhenNoRulesAreSetForAnyCombinationUpTheIdentityAndRoleTrees()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('admins');
        $repo->getIdentityParent('admins')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('admins', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('admins', 'ORDERS', null)->willReturn(null);

        $this->assertFalse($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckReturnsTrueWhenNoDirectRulesSetButParentIdentityIsGrantedToParentRole()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('admins');
        $repo->getIdentityParent('admins')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('admins', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('admins', 'ORDERS', null)->willReturn(Repository::STATE_GRANT);

        $this->assertTrue($judge->check('adam', 'ORDERS_EDIT'));
    }
}