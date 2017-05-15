<?php

namespace Judge;

class JudgeIntegrationTest extends TestCase
{
    /** @var Judge */
    private $judge;

    public function setUp()
    {
        $this->judge = new Judge;
    }

    public function test_roles_can_be_granted_for_specific_contexts()
    {
        $this->assertFalse($this->judge->check('adam', 'ORDERS', 'mycontext'));
        $this->assertFalse($this->judge->check('adam', 'ORDERS', 'secondcontext'));
        $this->judge->allow('adam', 'ORDERS', 'mycontext');
        $this->assertTrue($this->judge->check('adam', 'ORDERS', 'mycontext'));
        $this->assertFalse($this->judge->check('adam', 'ORDERS', 'secondcontext'));
    }
}
