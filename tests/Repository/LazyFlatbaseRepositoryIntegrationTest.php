<?php

namespace Judge\Repository;

class LazyFlatbaseRepositoryIntegrationTest extends FlatbaseRepositoryIntegrationTest
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
