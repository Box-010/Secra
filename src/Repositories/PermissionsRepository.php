<?php

namespace Secra\Repositories;


use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;

#[Provide(PermissionsRepository::class)]
#[Singleton]
class PermissionsRepository extends BaseRepository
{

}