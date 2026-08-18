<?php

namespace Modules\Tasks\Exceptions;

use App\Exceptions\DomainRuleViolation;

class InvalidTaskStatusTransition extends DomainRuleViolation {}
