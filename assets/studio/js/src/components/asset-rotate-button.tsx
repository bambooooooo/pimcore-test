import React, { useContext } from 'react'
import { Dropdown, DropdownButton, useAlertModal } from '@pimcore/studio-ui-bundle/components'
import { AssetContext, useAssetDraft } from '@pimcore/studio-ui-bundle/modules/asset'
import { useElementRefresh } from '@pimcore/studio-ui-bundle/modules/element'
import { send } from '../lib/api'
import { isRotatable } from '../lib/assets'
import { urls } from '../lib/urls'

/** Rotate images and PDFs (classic admin startup.js), served by AssetController::rotateLeftAction. */
export const AssetRotateButton = (): React.JSX.Element | null => {
  const { id } = useContext(AssetContext)
  const { asset } = useAssetDraft(id)
  const { refreshElement } = useElementRefresh('asset')
  const alertModal = useAlertModal()

  if (asset === undefined || !isRotatable(asset.type, (asset as { mimeType?: string | null }).mimeType)) return null

  const rotate = async (degrees: 90 | 180 | 270): Promise<void> => {
    try {
      await send(urls.rotateAsset(id, degrees))
      refreshElement(id, true)
    } catch (e) {
      alertModal.error({ title: 'Rotation failed', content: e instanceof Error ? e.message : String(e) })
    }
  }

  return (
    <Dropdown
      menu={ {
        items: [
          { key: '270', label: 'Rotate left (90)', onClick: () => { void rotate(270) } },
          { key: '90', label: 'Rotate right (90)', onClick: () => { void rotate(90) } },
          { key: '180', label: 'Rotate (180)', onClick: () => { void rotate(180) } }
        ]
      } }
    >
      <DropdownButton icon={ { value: 'refresh' } }>Rotate</DropdownButton>
    </Dropdown>
  )
}
