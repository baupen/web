<?php

namespace App\Enum;

enum IssueState: int
{
    /**
     * An Issue goes through the following states from the point of view of the user:
     * - new (created, but not registered yet)
     * - open (registered, but not resolved or closed)
     * - resolved (resolved, but not closed)
     * - closed (closed).
     *
     * The following states are also interesting:
     * - seen (opened < last visit of craftsman)
     * - overdue (deadline > resolved or (deadline > now && resolved == null))
     */
    case CREATED = 1;
    case REGISTERED = 2;
    case RESOLVED = 4;
    case CLOSED = 8;
}
