/* istanbul ignore file */

import {Translate} from '../DependenciesProvider.type';
// @ts-expect-error
import legacy from '../../legacy/translate';

const translate: Translate = legacy;

export {translate};
