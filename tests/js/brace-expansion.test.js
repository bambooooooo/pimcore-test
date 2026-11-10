import { describe, expect, it } from 'vitest';
import { expandBraces } from '../../assets/studio/js/src/lib/brace-expansion.ts';

describe('expandBraces - key patterns for "Add children (key pattern)..."', () => {
    it.each([
        ['plain key', 'SZAFA-80', ['SZAFA-80']],
        ['alternatives', 'SZAFA-{80,100}', ['SZAFA-80', 'SZAFA-100']],
        ['prefix and suffix', 'A{1,2}B', ['A1B', 'A2B']],
        ['several groups multiply', '{A,B}-{1,2}', ['A-1', 'A-2', 'B-1', 'B-2']],
        ['nested groups', 'K{1,2{a,b}}', ['K1', 'K2a', 'K2b']],
        ['single option stays literal', 'X{1}', ['X{1}']],
        ['empty option', 'P{,-L}', ['P', 'P-L']],
        ['three options', 'S-{80,100,120}', ['S-80', 'S-100', 'S-120']],
        ['unbalanced brace stays literal', 'A{1,2', ['A{1,2']],
        ['empty pattern', '', ['']],
    ])('%s: %s', (_, pattern, expected) => {
        expect(expandBraces(pattern)).toEqual(expected);
    });

    it('keeps escaped braces literal', () => {
        expect(expandBraces('A\\{1,2\\}')).toEqual(['A\\{1,2\\}']);
    });

    it('multiplies large patterns (the plugin rejects more than 500 keys)', () => {
        expect(expandBraces('{1,2,3,4,5}{1,2,3,4,5}{1,2,3,4,5}{1,2,3,4,5}')).toHaveLength(625);
    });
});
