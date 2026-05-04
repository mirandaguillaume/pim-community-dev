import {themes, yAxeTheme, daysAxeTheme, gridYAxesTheme} from '@src/audit/components/Chart/themes';

describe('themes', () => {
    it('exports blue, green, purple and red themes', () => {
        expect(themes).toHaveProperty('blue');
        expect(themes).toHaveProperty('green');
        expect(themes).toHaveProperty('purple');
        expect(themes).toHaveProperty('red');
    });

    it('each theme has line and scatter entries', () => {
        for (const theme of Object.values(themes)) {
            expect(theme).toHaveProperty('line');
            expect(theme).toHaveProperty('scatter');
        }
    });

    it('scatter labels have a fill color', () => {
        expect(themes.blue.scatter?.style?.labels).toHaveProperty('fill');
        expect(themes.red.scatter?.style?.labels).toHaveProperty('fill');
    });
});

describe('yAxeTheme', () => {
    it('hides tick labels', () => {
        expect(yAxeTheme.tickLabels.fill).toBe('none');
    });
});

describe('daysAxeTheme', () => {
    it('has no axis stroke', () => {
        expect(daysAxeTheme.axis.stroke).toBe('none');
    });
});

describe('gridYAxesTheme', () => {
    it('has no grid stroke', () => {
        expect(gridYAxesTheme.grid.stroke).toBe('none');
    });
});
