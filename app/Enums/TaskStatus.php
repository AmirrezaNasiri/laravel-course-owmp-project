<?php

namespace App\Enums;

enum TaskStatus: int
{
    /**
     * A task that has been identified and needs to be completed.
     */
    case TODO = 1;

    /**
     * A task that is currently being worked on.
     */
    case IN_PROGRESS = 2;

    /**
     * A task that cannot be completed until some other task or external event is resolved.
     */
    case BLOCKED = 3;

    /**
     * A task that has been finished successfully.
     */
    case COMPLETED = 4;

    /**
     * A task that has been postponed to a later time or date.
     */
    case DEFERRED = 5;

    /**
     * A task that has been abandoned or cancelled, typically due to changing priorities or unforeseen circumstances.
     */
    case CANCELLED = 6;

    /**
     * A task that has been completed but requires approval before it can be marked as finished.
     */
    case WAITING_FOR_APPROVAL = 7;

    /**
     * A task that has been put on hold temporarily due to some reason, such as resource constraints or waiting for more information.
     */
    case ON_HOLD = 8;
}
