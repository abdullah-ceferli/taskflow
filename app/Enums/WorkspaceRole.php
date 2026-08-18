<?php

namespace App\Enums;

enum WorkspaceRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Member = 'member';
}
