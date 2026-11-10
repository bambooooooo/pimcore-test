import { describe, expect, it } from 'vitest';
import { optimikQuestions } from '../../assets/studio/js/src/lib/optimik.ts';
import { qualityColor } from '../../assets/studio/js/src/lib/quality.ts';
import { isRotatable } from '../../assets/studio/js/src/lib/assets.ts';

describe('quality badge colour (same thresholds as the classic admin)', () => {
    it.each([
        [100, '#35c335'], [91, '#35c335'],
        [90, '#c3bc35'], [51, '#c3bc35'],
        [50, '#c33535'], [0, '#c33535'],
    ])('%i%% -> %s', (quality, colour) => {
        expect(qualityColor(quality)).toBe(colour);
    });
});

describe('Optimik export questions', () => {
    it('asks nothing when every check passes', () => {
        expect(optimikQuestions({ unpublished: [], quantity: [] })).toEqual([]);
        expect(optimikQuestions(undefined)).toEqual([]);
    });

    it('asks once about all unpublished products', () => {
        const [question, ...rest] = optimikQuestions({ unpublished: ['A', 'B'], quantity: [] });

        expect(rest).toEqual([]);
        expect(question).toContain('Product A is unpublished');
        expect(question).toContain('Product B is unpublished');
        expect(question).toMatch(/Do you want to proceed\?$/);
    });

    it('asks about quantities that are not a multiple of the serie size, after the publishing question', () => {
        const questions = optimikQuestions({
            unpublished: ['A'],
            quantity: [{ key: 'B', quantity: 7, serieSize: 5 }],
        });

        expect(questions).toHaveLength(2);
        expect(questions[1]).toContain('B: ordered quantity 7 is not multiplication of its serie size 5');
    });
});

describe('asset rotation availability', () => {
    it.each([
        ['image', 'image/png', true],
        ['document', 'application/pdf', true],
        ['document', 'text/csv', false],
        ['video', 'video/mp4', false],
    ])('%s %s -> %s', (type, mime, expected) => {
        expect(isRotatable(type, mime)).toBe(expected);
    });
});
