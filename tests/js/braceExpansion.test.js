import { beforeAll, describe, expect, it } from 'vitest';
import { createAdminEnv } from './harness.js';

describe('braceExpansion.js - key patterns for "add children" (Ctrl+M)', () => {
    beforeAll(() => {
        createAdminEnv().load('braceExpansion');
    });

    it.each([
        ['plain key', 'SZAFA-80', ['SZAFA-80']],
        ['alternatives', 'SZAFA-{80,100}', ['SZAFA-80', 'SZAFA-100']],
        ['prefix and suffix', 'A{1,2}B', ['A1B', 'A2B']],
        ['several groups multiply', '{A,B}-{1,2}', ['A-1', 'A-2', 'B-1', 'B-2']],
        ['nested groups', 'K{1,2{a,b}}', ['K1', 'K2a', 'K2b']],
        ['single option stays literal', 'X{1}', ['X{1}']],
        ['empty option', 'P{,-L}', ['P', 'P-L']],
    ])('%s: %s', (_, pattern, expected) => {
        expect(expansions(pattern)).toEqual(expected);
    });

    it('keeps escaped braces literal', () => {
        expect(expansions('A\\{1,2\\}')).toEqual(['A\\{1,2\\}']);
    });
});
