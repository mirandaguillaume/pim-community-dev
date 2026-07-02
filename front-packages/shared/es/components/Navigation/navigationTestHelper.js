import React from 'react';
import { CardIcon } from 'akeneo-design-system';
var subNavigationEntries = [
    {
        code: 'subentry1',
        sectionCode: 'section1',
        title: 'Sub entry 1',
        route: 'subentry1_route',
    },
    {
        code: 'subentry2',
        sectionCode: 'section1',
        title: 'Sub entry 2',
        route: 'subentry2_route',
    },
    {
        code: 'subentry3',
        sectionCode: 'section2',
        title: 'Sub entry 3',
        route: 'subentry3_route',
        disabled: true,
    },
];
var sections = [
    {
        code: 'section1',
        title: 'Section 1',
    },
    {
        code: 'section2',
        title: 'Section 2',
    },
];
var aSubNavigationMenu = function () {
    return {
        subNavigationEntries: subNavigationEntries,
        sections: sections,
    };
};
var aMainNavigation = function () {
    return [
        {
            code: 'entry1',
            title: 'Entry 1',
            route: 'entry1_route',
            icon: React.createElement(CardIcon, null),
            subNavigations: [
                {
                    entries: subNavigationEntries,
                    sections: sections,
                },
            ],
            align: 'bottom',
            isLandingSectionPage: false,
        },
        {
            code: 'entry2',
            title: 'Entry 2',
            route: 'entry2_route',
            icon: React.createElement(CardIcon, null),
            subNavigations: [],
            isLandingSectionPage: true,
        },
        {
            code: 'entry3',
            title: 'Entry 3',
            route: 'entry3_route',
            icon: React.createElement(CardIcon, null),
            subNavigations: [],
            isLandingSectionPage: false,
            disabled: true,
        },
    ];
};
export { aMainNavigation, aSubNavigationMenu };
//# sourceMappingURL=navigationTestHelper.js.map