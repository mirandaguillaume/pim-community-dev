<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component;

final class Profiles
{
    /** I manage product catalogs. */
    public const string PRODUCT_MANAGER = 'product_manager';

    /** I enrich product data. */
    public const string REDACTOR = 'redactor';

    /** I integrate Akeneo PIM into our IT ecosystem.  */
    public const string PIM_INTEGRATOR = 'pim_integrator';

    /** I administrate Akeneo PIM. */
    public const string PIM_ADMINISTRATOR = 'pim_administrator';

    /** I manage assets in the PIM. */
    public const string ASSET_MANAGER = 'asset_manager';

    /** I translate product, asset, and/or reference entity data. */
    public const string TRANSLATOR = 'translator';

    /** I develop solutions connected with Akeneo PIM. */
    public const string THIRD_PARTY_DEVELOPER = 'third_party_developer';
}
