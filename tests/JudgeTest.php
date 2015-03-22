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

    public function testCheckWithSingleLevelTreeReturnsTrueWhenGranted()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_GRANT);

        $this->assertTrue($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithSingleLevelTreeReturnsfalseWhenRevoked()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(Repository::STATE_REVOKE);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }

    public function testCheckWithSingleLevelTreeReturnsfalseWhenNotSet()
    {
        $repo = $this->prophesize('Judge\Repository\Repository');
        $judge = new Judge($repo->reveal());

        $repo->getIdentityParent('adam')->willReturn(null);
        $repo->getRuleState('adam', 'ORDERS', null)->willReturn(null);

        $this->assertFalse($judge->check('adam', 'ORDERS'));
    }
}