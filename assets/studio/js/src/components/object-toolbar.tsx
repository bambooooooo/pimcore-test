import React, { useContext, useEffect, useState } from 'react'
import { Button, Dropdown, DropdownButton, Flex, type ItemType } from '@pimcore/studio-ui-bundle/components'
import { DataObjectContext, useDataObjectDraft } from '@pimcore/studio-ui-bundle/modules/data-object'
import { getJson } from '../lib/api'
import { type ToolbarContext } from '../lib/types'
import { urls } from '../lib/urls'
import { GroupPriceListModal } from './group-price-list-modal'
import { LabelsModal } from './labels-modal'
import { OfferStatus } from './offer-status'
import { QualityBadge } from './quality-badge'
import { type ObjectActions, useObjectActions } from './use-object-actions'

export const SUPPORTED_CLASSES = ['Product', 'ProductSet', 'Group', 'Offer', 'User', 'Order', 'Package']

/**
 * Megstyl buttons in the data object editor toolbar (left slot). The toolbar context comes from
 * GET /studio-ext/object/{id}/toolbar and is reloaded whenever the object is saved or reloaded.
 */
export const ObjectToolbar = (): React.JSX.Element | null => {
  const { id } = useContext(DataObjectContext)
  const { dataObject } = useDataObjectDraft(id)
  const [ctx, setCtx] = useState<ToolbarContext | null>(null)
  const className = dataObject?.className
  const version = (dataObject as { modificationDate?: number } | undefined)?.modificationDate

  useEffect(() => {
    if (className === undefined || !SUPPORTED_CLASSES.includes(className)) return

    let active = true
    getJson<ToolbarContext>(urls.toolbarContext(id)).then(c => { if (active) setCtx(c) }).catch(() => { if (active) setCtx(null) })

    return () => { active = false }
  }, [id, className, version])

  if (ctx === null || ctx.id !== id) return null

  return <ToolbarButtons ctx={ ctx } />
}

const menu = (label: string, icon: string, items: ItemType[]): React.JSX.Element => (
  <Dropdown key={ label } menu={ { items } }>
    <DropdownButton icon={ { value: icon } }>{label}</DropdownButton>
  </Dropdown>
)

const item = (key: string, label: string, onClick: () => void): ItemType => ({ key, label, onClick })

const ToolbarButtons = ({ ctx }: { ctx: ToolbarContext }): React.JSX.Element => {
  const actions = useObjectActions(ctx)
  const [labelsOpen, setLabelsOpen] = useState(false)
  const [priceListOpen, setPriceListOpen] = useState(false)
  const [status, setStatus] = useState('Loading status...')

  const parts: React.ReactNode[] = []

  switch (ctx.className) {
    case 'Product':
    case 'ProductSet':
      if (ctx.quality !== null && ctx.quality !== undefined) {
        parts.push(<QualityBadge key="quality" quality={ ctx.quality } />)
      }
      parts.push(menu('Download', 'download', [
        item('images', 'Images', () => { actions.open(urls.productImages(ctx.id)) }),
        ...(ctx.className === 'Product' ? [item('fillments', 'Fillment spec (XLSX)', () => { actions.open(urls.productFillments(ctx.id)) })] : [])
      ]))
      parts.push(menu('Modify', 'edit', modifyItems(ctx, actions)))
      if (ctx.className === 'Product') {
        parts.push(<Button key="labels" onClick={ () => { setLabelsOpen(true) } }>Package label</Button>)
      }
      break

    case 'Group':
      parts.push(menu('Download', 'download', [
        item('images', 'Product(Set) images', () => { actions.openGroupImages() }),
        item('pricelist', 'Pricelist', () => { setPriceListOpen(true) })
      ]))
      parts.push(menu('Modify', 'edit', [
        item('translate-name', 'Translate name', () => { void actions.translate('name') }),
        item('translate-description', 'Translate description', () => { void actions.translate('description') })
      ]))
      break

    case 'Offer':
      parts.push(menu('Download', 'download', [
        item('xlsx', 'Price list (xlsx)', () => { actions.open(urls.offerPriceListXlsx(ctx.id)) }),
        item('pdf', 'Price list (PDF)', () => { actions.open(urls.offerPriceListPdf(ctx.id, ctx.language)) }),
        item('datasheets', 'Price list + datasheet (PDF)', () => { actions.open(urls.offerDatasheets(ctx.id, ctx.language)) })
      ]))
      parts.push(menu('Modify', 'refresh', [
        item('refresh-products', 'Refresh products', () => { void actions.patch(urls.offerRefreshProducts(ctx.id)) }),
        item('refresh-sets', 'Refresh sets', () => { void actions.patch(urls.offerRefreshSets(ctx.id)) })
      ]))
      parts.push(<Button key="feed" onClick={ () => { void actions.enqueueFeed(setStatus) } }>Feed</Button>)
      parts.push(<OfferStatus key="status" load={ actions.status } setText={ setStatus } text={ status } />)
      break

    case 'User':
      parts.push(menu('Download', 'download', [
        item('preview', 'Offers (preview)', () => { actions.openUserPriceList('preview') }),
        item('xlsx', 'Price list (xlsx)', () => { actions.openUserPriceList('xlsx') })
      ]))
      break

    case 'Order':
      parts.push(<Button key="optimik" onClick={ () => { void actions.exportOptimik() } }>Optimik 3</Button>)
      break

    case 'Package':
      parts.push(<Button key="labels" onClick={ () => { setLabelsOpen(true) } }>Package label</Button>)
      break
  }

  return (
    <Flex gap="extra-small">
      {parts}
      {(ctx.className === 'Product' || ctx.className === 'Package') && (
        <LabelsModal kind={ ctx.className } objectId={ ctx.id } onClose={ () => { setLabelsOpen(false) } } open={ labelsOpen } />
      )}
      {ctx.className === 'Group' && (
        <GroupPriceListModal
          groupId={ ctx.id }
          groupKey={ ctx.key }
          language={ ctx.language }
          onClose={ () => { setPriceListOpen(false) } }
          open={ priceListOpen }
        />
      )}
    </Flex>
  )
}

function modifyItems (ctx: ToolbarContext, actions: ObjectActions): ItemType[] {
  const items = [
    item('ean', 'EAN13 (GTIN)', () => { void actions.assignEan() }),
    item('translate-name', 'Translate name', () => { void actions.translate('name') })
  ]

  if (ctx.className === 'Product') {
    items.push(
      item('future-prices', 'Update future catalog prices', () => { void actions.computeCatalogPrices(false) }),
      item('common-prices', 'Update common catalog prices', () => { void actions.computeCatalogPrices(true) })
    )
  }

  return items
}
