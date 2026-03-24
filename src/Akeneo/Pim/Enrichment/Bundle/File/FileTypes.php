<?php

namespace Akeneo\Pim\Enrichment\Bundle\File;

/**
 * File types dictionary
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FileTypes
{
    /** @staticvar string */
    public const string DOCUMENT = 'pim_enrich_file_document';

    /** @staticvar string */
    public const string IMAGE = 'pim_enrich_file_image';

    /** @staticvar string */
    public const string VIDEO = 'pim_enrich_file_video';

    /** @staticvar string */
    public const string MISC = 'pim_enrich_file_misc';
}
