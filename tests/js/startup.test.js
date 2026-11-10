import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { click, createAdminEnv, EVENTS, findButton } from './harness.js';

describe('startup.js', () => {
    let env;

    beforeEach(() => {
        vi.useFakeTimers();
        env = createAdminEnv({ language: 'pl', websiteLanguages: ['pl', 'en', 'de'] }).load('startup');
    });

    afterEach(() => {
        env.expectAllUrlsRoutable();
        vi.useRealTimers();
    });

    describe('asset rotation', () => {
        it.each([
            ['Rotate left (90)', 270],
            ['Rotate right (90)', 90],
            ['Rotate (180)', 180],
        ])('%s rotates by %i degrees and reloads the asset', (label, degrees) => {
            const asset = env.openAsset('image', 'image/png', { id: 55 });

            click(findButton(findButton(asset.toolbar, 'Rotate').menu, label));
            env.respond(env.ajax[0], 'ok');

            expect(env.ajax[0].url).toBe(`/assets/rotate/55/${degrees}`);
            expect(asset.reload).toHaveBeenCalled();
        });

        it('is offered for PDFs', () => {
            expect(env.openAsset('document', 'application/pdf').toolbar.items).toHaveLength(1);
        });

        it('is not offered for other files', () => {
            expect(env.openAsset('document', 'text/csv').toolbar.items).toHaveLength(0);
        });
    });

    describe('products', () => {
        const openProduct = (data = {}) => env.openObject('Product', { ObjectType: 'ACTUAL', Quality: '95', Ean: null, ...data }, { id: 42 });

        it('downloads all product images', () => {
            const product = openProduct();

            click(findButton(findButton(product.toolbar, 'Download').menu, 'Images'));

            expect(env.opened).toEqual(['/export/images/42']);
        });

        it.each([
            ['95', '#35c335'],
            ['70', '#c3bc35'],
            ['10', '#c33535'],
        ])('shows quality %s%% in colour %s', (quality, colour) => {
            const product = openProduct({ Quality: quality });

            const badge = product.toolbar.items.find((i) => i.xtype === 'label');
            expect(badge.html).toContain(`width: ${quality}%`);
            expect(badge.html).toContain(colour);
        });

        it('assigns an EAN and reloads the product', () => {
            const product = openProduct();

            click(findButton(findButton(product.toolbar, 'Modify').menu, 'EAN13 (GTIN)'));
            env.respond(env.ajax[0], { remaining: 1000 });

            expect(env.ajax[0]).toMatchObject({ url: '/object/add-ean', method: 'POST', params: { id: 42 } });
            expect(product.reload).toHaveBeenCalled();
            expect(pimcore.helpers.showNotification).not.toHaveBeenCalled();
        });

        it('warns when the EAN pool is running out', () => {
            const product = openProduct();

            click(findButton(findButton(product.toolbar, 'Modify').menu, 'EAN13 (GTIN)'));
            env.respond(env.ajax[0], { remaining: 50 });

            expect(pimcore.helpers.showNotification).toHaveBeenCalledWith('Warning', expect.stringContaining('50'), 'warn');
        });

        it('does not assign a second EAN', () => {
            const product = openProduct({ Ean: '5901234123457' });

            click(findButton(findButton(product.toolbar, 'Modify').menu, 'EAN13 (GTIN)'));

            expect(env.ajax).toHaveLength(0);
            expect(env.messages[0]).toMatchObject({ type: 'alert', msg: 'EAN already exists!' });
        });

        it('translates the name into every website language after confirmation (source included - see report)', () => {
            const product = openProduct({ localizedfields: { data: { pl: { Name: 'Szafa' } } } });

            click(findButton(findButton(product.toolbar, 'Modify').menu, 'Translate name'));
            env.messages.find((m) => m.type === 'confirm').fn('yes');

            for (let i = 0; i < 10 && env.ajax.length < 3; i++) {
                env.respond(env.ajax[env.ajax.length - 1], 'ok');
                vi.runOnlyPendingTimers();
            }

            const targets = env.ajax.map((a) => a.params.loc);
            expect(env.ajax.every((a) => a.url === '/object/translate-name')).toBe(true);
            expect(env.ajax[0].params).toMatchObject({ id: 42, origin: 'pl', field: 'name', name: 'Szafa' });
            expect(targets).toEqual(['pl', 'en', 'de']);
        });

        it('translates nothing when the confirmation is declined', () => {
            const product = openProduct({ localizedfields: { data: { pl: { Name: 'Szafa' } } } });

            click(findButton(findButton(product.toolbar, 'Modify').menu, 'Translate name'));
            env.messages.find((m) => m.type === 'confirm').fn('no');

            expect(env.ajax).toHaveLength(0);
        });
    });

    describe('offers', () => {
        const openOffer = () => env.openObject('Offer', {}, { id: 3 });

        it.each([
            ['Price list (xlsx)', '/prices/3?kind=xlsx'],
            ['Price list + datasheet (PDF)', '/object/pl/3/datasheet?mode=detailed'],
        ])('%s opens %s', (label, url) => {
            const offer = openOffer();

            click(findButton(findButton(offer.toolbar, 'Download').menu, label));

            expect(env.opened).toEqual([url]);
        });

        it('price list PDF opens the detailed datasheet with prices', () => {
            const offer = openOffer();

            click(findButton(findButton(offer.toolbar, 'Download').menu, 'Price list (PDF)'));

            expect(env.opened[0]).toMatch(/^\/object\/pl\/3\/datasheet\?mode=detailed&show_pric/);
        });

        it('enqueues feed generation and shows its status', () => {
            const offer = openOffer();
            const status = env.components.find((c) => c.$class === 'Ext.Toolbar.TextItem');

            click(findButton(offer.toolbar, 'Feed'));
            const feed = env.ajax.find((a) => a.url === '/offer/feed/3');
            env.respond(feed, 'ok');

            expect(feed.method).toBe('GET');
            expect(status.text).toBe('Enqueued');
        });

        it('polls the offer status every 5 seconds (never stopped - see report)', () => {
            openOffer();
            const polls = () => env.ajax.filter((a) => a.url === '/objects/status/3').length;

            expect(polls()).toBe(1);
            vi.advanceTimersByTime(15000);
            expect(polls()).toBe(4);
        });

        it.each(['Modify', 'Refresh sets'])('offers product refresh actions (%s)', () => {
            const offer = openOffer();
            const refreshes = offer.toolbar.items.filter((i) => i.handler && /refresh/i.test(String(i.handler)));

            refreshes.forEach(click);

            expect(env.ajax.map((a) => a.url)).toEqual(expect.arrayContaining(['/offer/refresh-products/3', '/offer/refresh-sets/3']));
        });
    });

    describe('users', () => {
        it('downloads all offers of a user as one price list', () => {
            vi.setSystemTime(new Date(2026, 10, 10));
            const user = env.openObject('User', { Offers: [{ id: 3 }, { id: 4 }, { id: 5 }] }, { key: 'acme' });

            click(findButton(findButton(user.toolbar, 'Download').menu, 'Price list (xlsx)'));

            expect(env.opened).toEqual(['/prices/3?kind=xlsx&filename=acme-10.11.2026&references[]=4&references[]=5']);
        });

        it('explains when the user has no offers', () => {
            const user = env.openObject('User', { Offers: [] });

            click(findButton(findButton(user.toolbar, 'Download').menu, 'Offers (preview)'));

            expect(env.opened).toHaveLength(0);
            expect(env.messages[0]).toMatchObject({ type: 'alert', title: 'No offer assigned to the user' });
        });
    });

    describe('orders - Optimik export', () => {
        const openOrder = (products) => env.openObject('Order', { Products: products }, { id: 77 });

        it('exports a valid order straight away', async () => {
            const order = openOrder([{ key: 'A', published: true, Quantity: 10, OptimikExportSerieSize: 5 }]);

            await findButton(order.toolbar, 'Optimik 3').handler();

            expect(env.opened).toEqual(['/orders/optimik/77']);
        });

        it('asks about unpublished products and cancels on "cancel"', async () => {
            const order = openOrder([{ key: 'A', published: false, Quantity: 10 }]);

            const running = findButton(order.toolbar, 'Optimik 3').handler();
            const question = env.messages.find((m) => m.type === 'show');
            expect(question.msg).toContain('A is unpublished');
            question.fn('cancel');
            await running;

            expect(env.opened).toHaveLength(0);
            expect(pimcore.helpers.showNotification).toHaveBeenCalledWith('Operation cancelled by user');
        });

        it('asks about quantities that are not a multiple of the serie size', async () => {
            const order = openOrder([{ key: 'B', published: true, Quantity: 7, OptimikExportSerieSize: 5 }]);

            const running = findButton(order.toolbar, 'Optimik 3').handler();
            await vi.waitFor(() => expect(env.messages).toHaveLength(1));
            expect(env.messages[0].msg).toContain('B: ordered quantity 7 is not multiplication of its serie size');
            env.messages[0].fn('ok');
            await running;

            expect(env.opened).toEqual(['/orders/optimik/77']);
        });
    });

    describe('main menu', () => {
        const ready = (environment) => {
            pimcore.settings.environment = environment;
            const fileMenu = { items: [], insert: (item) => fileMenu.items.push(item) };
            env.globals.layout_toolbar = { fileMenu };
            env.fire(EVENTS.pimcoreReady);

            return fileMenu;
        };

        it('links the factory panel', () => {
            click(findButton(ready('dev'), 'Megstyl Factory'));

            expect(window.open).toHaveBeenCalledWith('https://pim.example.com/factory', '_blank');
        });

        it('links the production docs host in prod', () => {
            click(findButton(ready('prod'), 'Megstyl Docs'));

            expect(window.open).toHaveBeenCalledWith('https://docs.example.com', '_blank');
        });

        it('links the local docs port elsewhere', () => {
            click(findButton(ready('dev'), 'Megstyl Docs'));

            expect(window.open).toHaveBeenCalledWith('https://pim.example.com:8005', '_blank');
        });
    });
});
