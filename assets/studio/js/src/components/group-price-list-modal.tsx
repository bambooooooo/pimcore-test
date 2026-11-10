import React, { useState } from 'react'
import { Button, Checkbox, Flex, Modal, Select, Text, useAlertModal } from '@pimcore/studio-ui-bundle/components'
import { openInNewTab } from '../lib/api'
import { defaultGroupPriceListOptions, type GroupPriceListOptions, urls } from '../lib/urls'
import { useList } from './use-list'

interface Props {
  groupId: number
  groupKey: string
  language: string
  open: boolean
  onClose: () => void
}

const checkboxes: Array<[keyof GroupPriceListOptions, string]> = [
  ['showUnpublished', 'Show unpublished'],
  ['showProducts', 'Show products'],
  ['showSets', 'Show sets'],
  ['showRelatedProducts', 'Show related products'],
  ['showProductsTypeSku', 'Show products with type SKU'],
  ['showSummaryGrid', 'Show summary grid'],
  ['showProductStocks', 'Show products stocks'],
  ['showPrices', 'Show prices']
]

/** Price list / datasheets of a Group (classic admin groupPriceList.js). */
export const GroupPriceListModal = ({ groupId, groupKey, language, open, onClose }: Props): React.JSX.Element => {
  const alertModal = useAlertModal()
  const [options, setOptions] = useState<GroupPriceListOptions>(defaultGroupPriceListOptions)
  const priceLevels = useList(urls.priceLevels(), open)

  const download = (type?: 'xlsx' | 'pdf-spec'): void => {
    if (type !== 'pdf-spec' && (options.priceLevel ?? '') === '') {
      alertModal.warn({ title: 'Warning', content: 'Please specify price level' })
      return
    }

    openInNewTab(urls.groupPriceList(groupId, language, options, type))
  }

  return (
    <Modal
      footer={
        <Flex gap="small" justify="end">
          <Button onClick={ () => { download() } } type="primary">Price list PDF</Button>
          <Button onClick={ () => { download('xlsx') } }>Price list XLSX</Button>
          <Button onClick={ () => { download('pdf-spec') } }>Datasheets PDF</Button>
        </Flex>
      }
      iconName="price-tag"
      onCancel={ onClose }
      open={ open }
      title={ `Price list for ${groupKey}` }
    >
      <Flex gap="small" vertical>
        {checkboxes.map(([key, label]) => (
          <Checkbox
            checked={ options[key] as boolean }
            key={ key }
            onChange={ (e) => { setOptions({ ...options, [key]: e.target.checked }) } }
          >
            {label}
          </Checkbox>
        ))}
        <Text>Price level</Text>
        <Select
          allowClear
          onChange={ (priceLevel: string | undefined) => { setOptions({ ...options, priceLevel }) } }
          options={ priceLevels.map(l => ({ value: l.name, label: l.title ?? l.name })) }
          value={ options.priceLevel }
        />
      </Flex>
    </Modal>
  )
}
