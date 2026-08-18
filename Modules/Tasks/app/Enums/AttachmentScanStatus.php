<?php

namespace Modules\Tasks\Enums;

enum AttachmentScanStatus: string
{
    case NotScanned = 'not_scanned';
    case Clean = 'clean';
    case Infected = 'infected';
}
