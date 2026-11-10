import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { click, createAdminEnv, findButton } from './harness.js';

describe('labels.js - package label printout', () => {
    let env;

    beforeEach(() => {
        env = createAdminEnv().load('labels');
    });

    afterEach(() => env.expectAllUrlsRoutable());

    const combo = (label) => env.components.find((c) => c.$class === 'Ext.form.ComboBox' && c.config.fieldLabel === label);
    const showButton = () => env.components.find((c) => c.$class === 'Ext.Button' && c.config.text === 'Show');

    it('loads label sizes, customer templates and related objects', () => {
        env.openObject('Package', {}, { id: 5 });

        expect(env.storeUrls).toEqual(expect.arrayContaining(['/labelsize', '/userlabel', '/packageproducts/5', '/productpackages/5', '/common-orders']));
    });

    it.each(['Package', 'Product'])('adds the label button to %s', (className) => {
        const object = env.openObject(className);

        click(findButton(object.toolbar, 'Package label'));

        expect(env.components.find((c) => c.$class === 'Ext.window.Window').shown).toBe(true);
    });

    it('opens the label of a package for the selected product', () => {
        env.openObject('Package', {}, { id: 5 });
        combo('Template').value = '100x150';
        combo('Product').value = 77;

        click(showButton());

        expect(env.opened).toEqual(['/factory/pl/labels/5?product_id=77&size=100x150&copies=1']);
    });

    it('opens the label of a product for the selected package, customer template and order', () => {
        env.openObject('Product', {}, { id: 77 });
        combo('Template').value = '100x60';
        combo('Package').value = 5;
        combo('Recipient').value = 3;
        combo('Order').value = 11;

        click(showButton());

        expect(env.opened).toEqual(['/factory/pl/labels/5?product_id=77&size=100x60&copies=1&customer_id=3&serie_id=11']);
    });

    it.each([
        ['label size', 'Package', {}, 'Select label size first'],
        ['product', 'Package', { Template: '100x150' }, 'Select product first'],
        ['package', 'Product', { Template: '100x150' }, 'Select package first'],
    ])('asks for the %s first', (_, className, values, message) => {
        env.openObject(className);
        Object.entries(values).forEach(([label, value]) => (combo(label).value = value));

        click(showButton());

        expect(alert).toHaveBeenCalledWith(message);
        expect(env.opened).toHaveLength(0);
    });

    it('adds no button to other classes (but still loads its stores - see report)', () => {
        const offer = env.openObject('Offer', {}, { id: 8 });

        expect(offer.toolbar.items).toHaveLength(0);
        expect(env.storeUrls).toContain('/packageproducts/8');
    });
});
