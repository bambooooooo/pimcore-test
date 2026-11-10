/** Response of GET /studio-ext/object/{id}/toolbar (App\Studio\ToolbarContextBuilder). */
export interface ToolbarContext {
  id: number
  className: string
  key: string
  published: boolean
  language: string
  websiteLanguages: string[]

  // Product, ProductSet
  quality?: number | null
  hasEan?: boolean
  name?: string | null

  // Group
  description?: string | null
  productCount?: number

  // User
  offers?: number[]

  // Order
  optimikWarnings?: {
    unpublished: string[]
    quantity: Array<{ key: string, quantity: number, serieSize: number }>
  }
}

export type TranslatableField = 'name' | 'description'
