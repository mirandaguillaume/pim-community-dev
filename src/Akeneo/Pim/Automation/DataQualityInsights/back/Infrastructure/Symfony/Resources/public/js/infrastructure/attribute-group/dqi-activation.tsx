import React from 'react';
import {AttributeGroupDQIActivation} from '@akeneo-pim-community/data-quality-insights/src';

import BaseView from 'pimui/js/view/base';

class DQIActivation extends BaseView {
  public render() {
    this.renderReactElement(<AttributeGroupDQIActivation groupCode={this.getFormData()['code']} />, this.el);

    return this;
  }
}

export = DQIActivation;
