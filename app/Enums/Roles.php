<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Roles extends Enum
{
    const None = "";
    const Thief = "thief";
    const UndercoverThief = "undercover_thief";
    const Police = "police";

    public static $types = [self::Thief, self::UndercoverThief, self::Police];
}
