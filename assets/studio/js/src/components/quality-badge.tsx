import React from 'react'
import { Tooltip } from '@pimcore/studio-ui-bundle/components'
import { qualityColor } from '../lib/quality'

export const QualityBadge = ({ quality }: { quality: number }): React.JSX.Element => (
  <Tooltip title={ `Poziom jakości: ${quality}%` }>
    <div style={ { border: '1px solid #212121', borderRadius: 10, height: 20, width: 80, alignSelf: 'center' } }>
      <div
        data-testid="quality-badge"
        style={ {
          width: `${quality}%`,
          height: '100%',
          background: qualityColor(quality),
          color: '#fafafa',
          textAlign: 'center',
          fontSize: 12,
          lineHeight: '18px',
          borderRadius: 10
        } }
      >
        {quality}%
      </div>
    </div>
  </Tooltip>
)
