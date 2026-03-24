<?php

namespace Akeneo\UserManagement\Component;

class UserEvents
{
    final public const string PRE_CREATE_GROUP = 'pim.user.pre_create_group';
    final public const string POST_CREATE_GROUP = 'pim.user.post_create_group';

    final public const string PRE_UPDATE_GROUP = 'pim.user.pre_update_group';
    final public const string POST_UPDATE_GROUP = 'pim.user.post_update_group';

    final public const string PRE_DELETE_GROUP = 'pim.user.pre_delete_group';
    final public const string POST_DELETE_GROUP = 'pim.user.post_delete_group';
}
