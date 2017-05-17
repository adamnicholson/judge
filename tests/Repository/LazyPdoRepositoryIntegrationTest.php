<?php

namespace Judge\Repository;

class LazyPdoRepositoryIntegrationTest extends PDORepositoryIntegrationTest
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        return new LazyRepositoryWrapper(function () {
            return parent::getRepository();
        });
    }
}
