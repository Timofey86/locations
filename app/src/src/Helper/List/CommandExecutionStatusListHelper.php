<?php

namespace App\Helper\List;

use App\Helper\List\Shared\ListHelper;

class CommandExecutionStatusListHelper extends ListHelper
{
    public const string STATUS_BEGIN = 'begin';

    public const string STATUS_PROGRESS = 'progress';

    public const string STATUS_COMPLETE = 'complete';

    public const string STATUS_RESULT = 'result';

    public const string STATUS_ERROR = 'error';

    public static function getList(): array
    {
        return [
            self::STATUS_BEGIN => 'Begin execution',
            self::STATUS_PROGRESS => 'Progress execution',
            self::STATUS_COMPLETE => 'Complete execution',
            self::STATUS_RESULT => 'Result execution',
            self::STATUS_ERROR => 'End execution with error'
        ];
    }
}
