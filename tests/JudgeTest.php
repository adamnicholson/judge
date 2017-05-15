<?php

namespace Judge;

use Judge\Repository\Repository;

class JudgeTest extends TestCase
{
    public function testInstance()
    {
        $repo = $this->prophesize(Repository::class);
        $this->assertInstanceOf('Judge\Judge', new Judge($repo->reveal()));
    }

    public function testGrantUpdatesRepository()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_ALLOW)->shouldBeCalled();
        $judge->allow('adam', 'ORDERS');
    }

    public function testRevokeUpdatesRepository()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());
        $repo->saveRule('adam', 'ORDERS', null, Repository::STATE_DENY)->shouldBeCalled();
        $judge->deny('adam', 'ORDERS');
    }

    public function testGetRepositoryReturnsRepo()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());
        $this->assertEquals($judge->getRepository(), $repo->reveal());
    }

    public function testCheckWithSingleLevelIdentityTreeReturnsTrueWhenGranted()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_ALLOW);

        $this->assertTrue($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithSingleLevelIdentityTreeReturnsFalseWhenRevoked()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_DENY);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithSingleLevelIdentityTreeReturnsFalseWhenNotSet()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelIdentityTreeReturnsTrueWhenRuleNotSetForUserButGrantedForParent()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('administrator');
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('administrator', 'ORDERS', null)->willReturn(Repository::STATE_ALLOW);

        $this->assertTrue($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelIdentityTreeReturnsFalseWhenRuleNotSetForUserButRevokedForParent()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('administrator');
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('administrator', 'ORDERS', null)->willReturn(Repository::STATE_DENY);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithMultiLevelIdentityTreeReturnsFalseWhenRuleNotSetForIdentityOrParents()
    {
        $repo = $this->prophesize(Repository::class);
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
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_ALLOW);

        $this->assertTrue($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckWithMultiLevelRoleTreeReturnsFalseWhenNotSetForRoleButRevokedForParent()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_DENY);

        $this->assertFalse($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckWithMultiLevelRoleTreeReturnsFalseWhenNotSetForRoleOrParentRoles()
    {
        $repo = $this->prophesize(Repository::class);
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
        $repo = $this->prophesize(Repository::class);
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
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn('admins');
        $repo->getIdentityParent('admins')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('admins', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);
        $repo->getRuleState('admins', 'ORDERS', null)->willReturn(Repository::STATE_ALLOW);

        $this->assertTrue($judge->check('adam', 'ORDERS_EDIT'));
    }

    public function testCheckReturnsTrueWhereNoRuleSetForSpecificContextButRuleWithoutContextIsGranted()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', 5)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(Repository::STATE_ALLOW);

        $this->assertTrue($judge->check('adam', 'ORDERS_EDIT', 5));
    }

    public function testThatContextIsExcludedFromParentRoleChecks()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRoleParent('ORDERS_EDIT')->willReturn('ORDERS');
        $repo->getRoleParent('ORDERS')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', 5)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS_EDIT', null)->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_ALLOW);

        $this->assertTrue($judge->check('adam', 'ORDERS_EDIT', 5));
    }

    public function testEnforceThrowsExceptionWhenCheckFails()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new JudgeTestStubAlwaysReturningFalseToCheck($repo->reveal());

        $this->setExpectedException(Exception\NotAuthorizedException::class);

        $judge->enforce('adam', 'ORDERS_EDIT', 5);
    }

    public function testEnforceReturnsTrueWhenCheckPasses()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new JudgeTestStubAlwaysReturningTrueToCheck($repo->reveal());

        $this->assertTrue($judge->enforce('adam', 'ORDERS_EDIT', 5));
    }

    public function testAttemptCallableFiredWhenCheckPasses()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new JudgeTestStubAlwaysReturningTrueToCheck($repo->reveal());

        $marker = 1;
        $result = $judge->attempt('adam', 'ORDERS_EDIT', 5, function() use (&$marker) {
                $marker++;
                return 'foobar';
        });

        $this->assertEquals($marker, 2);
        $this->assertEquals($result, 'foobar');
    }

    public function testAttemptCallableNotFiredWhenCheckPasses()
    {
        $repo = $this->prophesize(Repository::class);
        $judge = new JudgeTestStubAlwaysReturningFalseToCheck($repo->reveal());

        $marker = 1;
        $result = $judge->attempt('adam', 'ORDERS_EDIT', 5, function() use (&$marker) {
                $marker++;
        });

        $this->assertEquals($marker, 1);
        $this->assertFalse($result);
    }
}

class JudgeTestStubAlwaysReturningTrueToCheck extends Judge
{
    public function check($identity, $role, $context = null)
    {
        return true;
    }
}

class JudgeTestStubAlwaysReturningFalseToCheck extends Judge
{
    public function check($identity, $role, $context = null)
    {
        return false;
    }
}
