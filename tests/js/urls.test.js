import { describe, expect, it } from 'vitest';
import { ddmmyyyy, defaultGroupPriceListOptions, urls } from '../../assets/studio/js/src/lib/urls.ts';
import { matchRoute } from './routes.js';

/**
 * Every URL the Studio plugin opens or calls must hit the intended Symfony route - moving an endpoint
 * without updating assets/studio fails here (routes.json comes from the real application).
 */
describe('Studio plugin URLs are routable', () => {
    const options = { ...defaultGroupPriceListOptions, priceLevel: 'price_new_base' };

    it.each([
        ['GET', urls.toolbarContext(42), 'studio_ext_object_toolbar'],
        ['POST', urls.addChildren(42), 'studio_ext_object_add_children'],
        ['GET', urls.rotateAsset(55, 270), 'assets_rotate'],
        ['GET', urls.productImages(42), 'export_images'],
        ['GET', urls.productFillments(42), 'products_fillments'],
        ['POST', urls.addEan(), 'add_ean'],
        ['POST', urls.computeCatalogPrice(), 'update_prices'],
        ['POST', urls.translateName(), 'translate_name'],
        ['GET', urls.offerPriceListXlsx(3), 'prices'],
        ['GET', urls.offerPriceListPdf(3, 'pl'), 'datasheet_new'],
        ['GET', urls.offerDatasheets(3, 'pl'), 'datasheet_new'],
        ['PATCH', urls.offerRefreshProducts(3), 'feed_refresh_products'],
        ['PATCH', urls.offerRefreshSets(3), 'feed_refresh_sets'],
        ['GET', urls.offerFeed(3), 'feed_generate'],
        ['GET', urls.objectStatus(3), 'objects_status'],
        ['GET', urls.optimikExport(77), '_optimik'],
        ['GET', urls.priceLevels(), '_price_levels_head'],
        ['GET', urls.labelSizes(), 'get_labelsize'],
        ['GET', urls.labelRecipients(), 'get_userlabel'],
        ['GET', urls.packageProducts(9), 'get_packageproducts'],
        ['GET', urls.productPackages(42), 'get_product_packages'],
        ['GET', urls.commonOrders(), 'get_common_orders'],
        ['GET', urls.userPriceList([3, 4], 'acme', 'xlsx'), 'prices'],
        ['GET', urls.groupPriceList(12, 'pl', options), '_price_list'],
        ['GET', urls.label(9, 42, { size: '150x60' }), 'factory_labels'],
    ])('%s %s -> %s', (method, url, route) => {
        expect(matchRoute(url, method)?.name).toBe(route);
    });

    it('opens the factory panel', () => {
        expect(matchRoute(urls.factory() + '/pl')?.name).toBe('factory_home');
    });
});

describe('URL parameters', () => {
    it('builds the price list of all offers of a customer', () => {
        expect(urls.userPriceList([3, 4, 5], 'acme', 'xlsx', new Date(2026, 10, 10)))
            .toBe('/prices/3?kind=xlsx&filename=acme-10.11.2026&references[]=4&references[]=5');
        expect(urls.userPriceList([], 'acme', 'preview')).toBeNull();
    });

    it('builds the offer price list PDF with prices of the offer', () => {
        expect(urls.offerPriceListPdf(3, 'pl')).toBe('/object/pl/3/datasheet?mode=detailed&show_prices=3');
    });

    it('passes every group price list option', () => {
        const url = new URL(urls.groupPriceList(12, 'de', { ...defaultGroupPriceListOptions, priceLevel: 'base' }, 'xlsx'), 'https://pim');

        expect(url.pathname).toBe('/object/de/12/price-list');
        expect(Object.fromEntries(url.searchParams)).toEqual({
            show_unpublished: 'false',
            show_products: 'true',
            show_sets: 'true',
            show_related_products: 'true',
            show_products_type_sku: 'false',
            show_summary_grid: 'true',
            show_product_stocks: 'false',
            show_prices: 'true',
            type: 'xlsx',
            price_level: 'base',
        });
    });

    it('omits an empty price level (datasheets do not need one)', () => {
        expect(urls.groupPriceList(12, 'pl', defaultGroupPriceListOptions, 'pdf-spec')).not.toContain('price_level');
    });

    it('builds label URLs with optional recipient and order', () => {
        expect(urls.label(9, 42, { size: '150x60' })).toBe('/factory/pl/labels/9?product_id=42&size=150x60&copies=1');
        expect(urls.label(9, 42, { size: '150x60', customerId: 7, serieId: 8 }))
            .toBe('/factory/pl/labels/9?product_id=42&size=150x60&copies=1&customer_id=7&serie_id=8');
    });

    it('links the docs host in production and port 8005 elsewhere', () => {
        expect(urls.docs('https://pim.megstyl.com')).toBe('https://docs.megstyl.com');
        expect(urls.docs('http://localhost')).toBe('http://localhost:8005');
    });

    it('formats dates like the classic admin (dd.mm.yyyy)', () => {
        expect(ddmmyyyy(new Date(2026, 0, 5))).toBe('05.01.2026');
    });
});
