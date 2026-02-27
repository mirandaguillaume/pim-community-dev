"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getMockCatalogVolume = void 0;
var catalogVolumeWrapper_1 = require("./catalogVolumeWrapper");
var volumes = {
    count_attributes: {
        value: 112,
        has_warning: false,
        type: 'count',
    },
    count_categories: {
        value: 289,
        has_warning: false,
        type: 'count',
    },
    count_category_trees: {
        value: 6,
        has_warning: true,
        type: 'count',
    },
    count_channels: {
        value: 5,
        has_warning: true,
        type: 'count',
    },
    count_families: {
        value: 108,
        has_warning: false,
        type: 'count',
    },
    count_locales: {
        value: 4,
        has_warning: false,
        type: 'count',
    },
    count_localizable_and_scopable_attributes: {
        value: 5,
        has_warning: false,
        type: 'count',
    },
    count_localizable_attributes: {
        value: 13,
        has_warning: false,
        type: 'count',
    },
    count_scopable_attributes: {
        value: 3,
        has_warning: false,
        type: 'count',
    },
    count_products: {
        value: 1389,
        has_warning: false,
        type: 'count',
    },
    count_product_models: {
        value: 100,
        has_warning: false,
        type: 'count',
    },
    count_variant_products: {
        value: 261,
        has_warning: false,
        type: 'count',
    },
    count_product_and_product_model_values: {
        value: 9200,
        has_warning: false,
        type: 'count',
    },
    count_reference_entity: {
        value: 7,
        has_warning: false,
        type: 'count',
    },
    count_asset_family: {
        value: 8,
        has_warning: false,
        type: 'count',
    },
    average_max_attributes_per_family: {
        value: {
            average: 4,
            max: 43,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_options_per_attribute: {
        value: {
            average: 69,
            max: 1007,
        },
        has_warning: true,
        type: 'average_max',
    },
    average_max_product_and_product_model_values: {
        value: {
            average: 7,
            max: 22,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_records_per_reference_entity: {
        value: {
            average: 1432,
            max: 10002,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_attributes_per_reference_entity: {
        value: {
            average: 6,
            max: 9,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_number_of_values_per_record: {
        value: {
            average: 6,
            max: 9,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_number_of_values_per_asset: {
        value: {
            average: 3,
            max: 4,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_assets_per_asset_family: {
        value: {
            average: 7,
            max: 21,
        },
        has_warning: false,
        type: 'average_max',
    },
    average_max_attributes_per_asset_family: {
        value: {
            average: 4,
            max: 5,
        },
        has_warning: false,
        type: 'average_max',
    },
};
var getMockCatalogVolume = function () { return __awaiter(void 0, void 0, void 0, function () {
    return __generator(this, function (_a) {
        return [2, (0, catalogVolumeWrapper_1.transformVolumesToAxis)(volumes)];
    });
}); };
exports.getMockCatalogVolume = getMockCatalogVolume;
//# sourceMappingURL=getMockCatalogVolume.js.map