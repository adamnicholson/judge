<?php

namespace Judge\Repository;

class LazyArrayRepositoryIntegrationTest extends ArrayRepositoryIntegrationTest
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        return new LazyRepositoryWrapper(function () {
            return new ArrayRepository;
        });
    }
}
