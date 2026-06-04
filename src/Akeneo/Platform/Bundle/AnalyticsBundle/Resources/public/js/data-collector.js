import $ from 'jquery';
import 'underscore';
import Routing from 'routing';

export default {
  collect: function (route) {
    return $.getJSON(Routing.generate(route));
  },
};
