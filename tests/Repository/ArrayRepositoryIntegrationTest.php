<?php

namespace Judge\Repository;

class ArrayRepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        return new ArrayRepository();
    }
}