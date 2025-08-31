<?php

namespace App\Helper\Factory;

use App\Helper\Factory\Shared\DomainEntityFactoryHelper;

class UpsertCommandDTOFactoryHelper extends DomainEntityFactoryHelper
{
    public static $patterns = ['\\Command\\Upsert\\Upsert%sDto', '\\Command\\Create\\Create%sCommandDto'];
}
