import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { click, createAdminEnv, findButton } from './harness.js';

describe('productFillmentSpec.js', () => {
    let env;

    beforeEach(() => {
        env = createAdminEnv().load('productFillmentSpec');
    });

    afterEach(() => env.expectAllUrlsRoutable());

    it('adds the fillment XLSX download to products', () => {
        const product = env.openObject('Product', {}, { id: 42 });

        click(findButton(product.toolbar, 'Download XLSX of product fillment from all its packages'));

        expect(env.opened).toEqual(['/products/fillments/42?kind=xlsx']);
    });

    it.each(['ProductSet', 'Package', 'Offer'])('adds nothing to %s', (className) => {
        expect(env.openObject(className).toolbar.items).toHaveLength(0);
    });
});
