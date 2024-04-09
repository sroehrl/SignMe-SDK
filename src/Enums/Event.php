<?php

namespace Retech\Celest\SignMe\Enums;

enum Event: string
{

    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

}
