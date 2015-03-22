<?php

namespace Judge\Repository;

use Flatbase\Flatbase;
use Flatbase\Storage\Filesystem;

class FlatbaseRepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        $flatbase = new Flatbase(new Filesystem(__DIR__ . '/data'));

        $flatbase->delete()->in('roles')->execute();
        $flatbase->delete()->in('identities')->execute();
        $flatbase->delete()->in('rules')->execute();

        return new FlatbaseRepository($flatbase);
    }
}
