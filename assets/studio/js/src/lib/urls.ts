/**
 * URLs opened or called by the Megstyl Studio plugin. All of them are listed in tests/Support/Data/url_contract.yaml;
 * tests/js/urls.test.js checks that each one is routable.
 */
const enc = encodeURIComponent

export const urls = {
  toolbarContext: (id: number) => `/studio-ext/object/${id}/toolbar`,
  addChildren: (id: number) => `/studio-ext/object/${id}/children`,

  rotateAsset: (id: number, degrees: 90 | 180 | 270) => `/assets/rotate/${id}/${degrees}`,

  productImages: (id: number) => `/export/images/${id}`,
  productFillments: (id: number) => `/products/fillments/${id}?kind=xlsx`,
  addEan: () => '/object/add-ean',
  computeCatalogPrice: () => '/object/compute-catalog-price',
  translateName: () => '/object/translate-name',

  offerPriceListXlsx: (id: number) => `/prices/${id}?kind=xlsx`,
  offerPriceListPdf: (id: number, language: string) => `/object/${language}/${id}/datasheet?mode=detailed&show_prices=${id}`,
  offerDatasheets: (id: number, language: string) => `/object/${language}/${id}/datasheet?mode=detailed`,
  offerRefreshProducts: (id: number) => `/offer/refresh-products/${id}`,
  offerRefreshSets: (id: number) => `/offer/refresh-sets/${id}`,
  offerFeed: (id: number) => `/offer/feed/${id}`,
  objectStatus: (id: number) => `/objects/status/${id}`,

  optimikExport: (id: number) => `/orders/optimik/${id}`,

  priceLevels: () => '/price-levels',
  labelSizes: () => '/labelsize',
  labelRecipients: () => '/userlabel',
  packageProducts: (packageId: number) => `/packageproducts/${packageId}`,
  productPackages: (productId: number) => `/productpackages/${productId}`,
  commonOrders: () => '/common-orders',

  factory: () => '/factory',

  /** All offers of a customer (User object) in one price list; kind "preview" or "xlsx". */
  userPriceList (offers: number[], userKey: string, kind: 'preview' | 'xlsx', today: Date = new Date()): string | null {
    if (offers.length === 0) return null

    const [first, ...references] = offers
    let url = `/prices/${first}?kind=${kind}&filename=${enc(userKey)}-${ddmmyyyy(today)}`

    for (const reference of references) {
      url += `&references[]=${reference}`
    }

    return url
  },

  /** Price list of a Group (PDF, XLSX or datasheets); see GroupPriceListModal for the options. */
  groupPriceList (id: number, language: string, options: GroupPriceListOptions, type?: 'xlsx' | 'pdf-spec'): string {
    const params = new URLSearchParams({
      show_unpublished: String(options.showUnpublished),
      show_products: String(options.showProducts),
      show_sets: String(options.showSets),
      show_related_products: String(options.showRelatedProducts),
      show_products_type_sku: String(options.showProductsTypeSku),
      show_summary_grid: String(options.showSummaryGrid),
      show_product_stocks: String(options.showProductStocks),
      show_prices: String(options.showPrices)
    })

    if (type !== undefined) params.set('type', type)
    if (options.priceLevel !== undefined && options.priceLevel !== '') params.set('price_level', options.priceLevel)

    return `/object/${language}/${id}/price-list?${params.toString()}`
  },

  /** Package label printout; either side (product or package) comes from the open object. */
  label (packageId: number, productId: number, options: { size: string, customerId?: number | null, serieId?: number | null }): string {
    const params = new URLSearchParams({ product_id: String(productId), size: options.size, copies: '1' })

    if (options.customerId != null) params.set('customer_id', String(options.customerId))
    if (options.serieId != null) params.set('serie_id', String(options.serieId))

    return `/factory/pl/labels/${packageId}?${params.toString()}`
  },

  /** Megstyl docs: https://pim.<domain> -> https://docs.<domain>, other hosts (dev) -> port 8005. */
  docs (origin: string): string {
    return /^https:\/\/pim\./.test(origin) ? origin.replace('https://pim.', 'https://docs.') : `${origin}:8005`
  }
}

export interface GroupPriceListOptions {
  showUnpublished: boolean
  showProducts: boolean
  showSets: boolean
  showRelatedProducts: boolean
  showProductsTypeSku: boolean
  showSummaryGrid: boolean
  showProductStocks: boolean
  showPrices: boolean
  priceLevel?: string
}

export const defaultGroupPriceListOptions: GroupPriceListOptions = {
  showUnpublished: false,
  showProducts: true,
  showSets: true,
  showRelatedProducts: true,
  showProductsTypeSku: false,
  showSummaryGrid: true,
  showProductStocks: false,
  showPrices: true
}

export function ddmmyyyy (date: Date): string {
  const pad = (n: number): string => String(n).padStart(2, '0')

  return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}`
}
