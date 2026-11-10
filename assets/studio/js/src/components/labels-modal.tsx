import React, { useState } from 'react'
import { Button, Flex, Modal, Select, Text, useAlertModal } from '@pimcore/studio-ui-bundle/components'
import { openInNewTab } from '../lib/api'
import { urls } from '../lib/urls'
import { useList } from './use-list'

interface Props {
  objectId: number
  /** The open object is a Package (choose the product) or a Product (choose the package). */
  kind: 'Package' | 'Product'
  open: boolean
  onClose: () => void
}

/** Package label printout (classic admin labels.js). */
export const LabelsModal = ({ objectId, kind, open, onClose }: Props): React.JSX.Element => {
  const alertModal = useAlertModal()
  const [counterpart, setCounterpart] = useState<number>()
  const [size, setSize] = useState<string>()
  const [customerId, setCustomerId] = useState<number>()
  const [serieId, setSerieId] = useState<number>()

  const counterparts = useList(kind === 'Package' ? urls.packageProducts(objectId) : urls.productPackages(objectId), open)
  const sizes = useList(urls.labelSizes(), open)
  const recipients = useList(urls.labelRecipients(), open)
  const orders = useList(urls.commonOrders(), open)

  const options = (items: typeof sizes, value: 'id' | 'name' = 'id') => items.map(i => ({ value: i[value], label: i.name }))

  const show = (): void => {
    if (size === undefined) {
      alertModal.warn({ title: 'Warning', content: 'Select label size first' })
      return
    }

    if (counterpart === undefined) {
      alertModal.warn({ title: 'Warning', content: kind === 'Package' ? 'Select product first' : 'Select package first' })
      return
    }

    const [packageId, productId] = kind === 'Package' ? [objectId, counterpart] : [counterpart, objectId]
    openInNewTab(urls.label(packageId, productId, { size, customerId, serieId }))
  }

  const field = (label: string, control: React.ReactNode): React.JSX.Element => (
    <Flex gap="small" vertical>
      <Text>{label}</Text>
      {control}
    </Flex>
  )

  return (
    <Modal
      footer={ <Button onClick={ show } type="primary">Show</Button> }
      iconName="print"
      onCancel={ onClose }
      open={ open }
      title="Package label"
    >
      <Flex gap="small" vertical>
        {field(kind === 'Package' ? 'Product' : 'Package',
          <Select onChange={ setCounterpart } options={ options(counterparts) } showSearch optionFilterProp="label" value={ counterpart } />)}
        {field('Template', <Select onChange={ setSize } options={ options(sizes, 'name') } value={ size } />)}
        {field('Recipient', <Select allowClear onChange={ setCustomerId } options={ options(recipients) } showSearch optionFilterProp="label" value={ customerId } />)}
        {field('Order', <Select allowClear onChange={ setSerieId } options={ options(orders) } showSearch optionFilterProp="label" value={ serieId } />)}
      </Flex>
    </Modal>
  )
}
