<?php

namespace App\Enum;

enum MembershipRole: string
{
    case OWNER = 'OWNER';
    case MEMBER = 'MEMBER';
}
