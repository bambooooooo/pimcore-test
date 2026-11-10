import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { click, createAdminEnv, findButton } from './harness.js';

describe('groupPriceList.js - downloads on Group objects', () => {
    let env;

    beforeEach(() => {
        env = createAdminEnv({ language: 'en' }).load('groupPriceList');
    });

    afterEach(() => env.expectAllUrlsRoutable());

    const openGroup = (products = [{ id: 1 }], sets = []) => env.openObject('Group', { Products: products, Sets: sets }, { id: 9 });
    const downloadMenu = (group) => findButton(group.toolbar, 'Download').menu;
    const component = (cls, name) => env.components.find((c) => c.$class === cls && (c.config.name === name || c.config.text === name));

    it('loads price levels for the selector', () => {
        openGroup();

        expect(env.storeUrls).toContain('/price-levels');
    });

    it('downloads the image ZIP of all group products', () => {
        const group = openGroup();

        click(findButton(downloadMenu(group), 'Product(Set) images'));

        expect(env.opened).toEqual(['/export/images/9']);
    });

    it('warns instead of downloading images of an empty group', () => {
        const group = openGroup([], []);

        click(findButton(downloadMenu(group), 'Product(Set) images'));

        expect(env.opened).toHaveLength(0);
        expect(env.messages[0]).toMatchObject({ type: 'alert', msg: 'Group has no Products and Sets!' });
    });

    it('opens the price list dialog', () => {
        const group = openGroup();

        click(findButton(downloadMenu(group), 'Pricelist'));

        expect(env.components.find((c) => c.$class === 'Ext.window.Window').shown).toBe(true);
    });

    it('requires a price level before generating the PDF', () => {
        openGroup();

        click(component('Ext.Button', 'Price list PDF'));

        expect(alert).toHaveBeenCalledWith('Please specify price level');
        expect(env.opened).toHaveLength(0);
    });

    it('passes every option and the price level to the price list URL', () => {
        openGroup();
        env.components.find((c) => c.config.fieldLabel === 'Select price level').value = 'retail';
        component('Ext.form.Checkbox', 'show_unpublished').value = true;

        click(component('Ext.Button', 'Price list PDF'));

        const url = new URL(env.opened[0], window.location.origin);
        expect(url.pathname).toBe('/object/en/9/price-list');
        expect(Object.fromEntries(url.searchParams)).toMatchObject({
            show_unpublished: 'true',
            show_products: 'true',
            show_sets: 'true',
            show_related_products: 'true',
            show_products_type_sku: 'false',
            show_summary_grid: 'true',
            show_product_stocks: 'false',
            show_prices: 'true',
            price_level: 'retail',
        });
    });

    it.each(['Product', 'Offer', 'Package'])('adds nothing to %s', (className) => {
        expect(env.openObject(className).toolbar.items).toHaveLength(0);
    });
});
