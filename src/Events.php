<?php

namespace Loconox\EntityRoutingBundle;

final class Events
{
    const ACTION_CREATE_SLUG = 'loconox_entity_routing.slug.action.create';

    const ACTION_UPDATE_SLUG = 'loconox_entity_routing.slug.action.update';

    const UNIQUE_SLUG_VIOLATION = 'loconox_entity_routing.event.unique_slug_violation';
}
