import BaseView = require('pimui/js/view/base');
import React from 'react';
import {AttributeGuidelinesApp} from '@akeneo-pim-community/settings-ui';

const propertyAccessor = require('pim/common/property');

class Guidelines extends BaseView {
  initialize(): void {
    BaseView.prototype.initialize.apply(this, []);
  }

  render(): any {
    const onChange = (newGuidelines: {[key: string]: string}) => {
      const data = this.getFormData();
      propertyAccessor.updateProperty(data, 'guidelines', newGuidelines);
      this.setData(data);
    };

    this.renderReactElement(
      <AttributeGuidelinesApp defaultValue={this.getFormData().guidelines || {}} onChange={onChange} />,
      this.el
    );
    return this;
  }

  remove() {
    this.unmountReact();

    return super.remove();
  }
}

export = Guidelines;
