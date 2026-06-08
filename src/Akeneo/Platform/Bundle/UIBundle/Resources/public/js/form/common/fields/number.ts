import _ from 'underscore';
import BaseText from 'pim/form/common/fields/text';
import template from 'pim/template/form/common/fields/number';

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NumberField extends BaseText {
  readonly template = _.template(template);
}

export = NumberField;
