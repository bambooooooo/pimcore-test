import fs from 'node:fs';
import path from 'node:path';
import { describe, expect, it } from 'vitest';
import { createAdminEnv, matchRoute, SCRIPT_DIR } from './harness.js';

const LISTENER = path.resolve(import.meta.dirname, '../../src/EventListener/AdminStyleListener.php');

const scripts = fs.readdirSync(SCRIPT_DIR).filter((f) => f.endsWith('.js')).sort();
const registered = [...fs.readFileSync(LISTENER, 'utf8').matchAll(/'\/static\/js\/([^']+\.js)'/g)].map((m) => m[1]);

describe('admin script registration', () => {
    it('registers every script in public/static/js with the admin UI', () => {
        expect([...registered].sort()).toEqual(scripts);
    });

    it('loads braceExpansion.js before addChildren.js, which uses expansions()', () => {
        expect(registered.indexOf('braceExpansion.js')).toBeLessThan(registered.indexOf('addChildren.js'));
    });

    it.each(scripts)('%s evaluates without errors', (file) => {
        const env = createAdminEnv();

        expect(() => env.load(file.replace(/\.js$/, ''))).not.toThrow();
    });
});

describe('route matcher', () => {
    it('matches app routes with placeholders and ignores query strings', () => {
        expect(matchRoute('/object/pl/12/price-list?show_prices=true')?.name).toBe('_price_list');
        expect(matchRoute('/objects/stocks', 'POST')?.name).toBe('stock_bulk_update');
    });

    it('rejects unknown paths and wrong methods', () => {
        expect(matchRoute('/no/such/endpoint')).toBeNull();
        expect(matchRoute('/objects/stocks', 'GET')).toBeNull();
    });
});
