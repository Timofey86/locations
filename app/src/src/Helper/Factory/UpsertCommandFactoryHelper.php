<?php

namespace App\Helper\Factory;

use App\Helper\Factory\Shared\DomainEntityFactoryHelper;

class UpsertCommandFactoryHelper extends DomainEntityFactoryHelper
{
    public static $patterns = ['\\Command\\Upsert\\Upsert%sCommand', '\\Command\\Create\\Create%sCommand'];
}
