import React, { useEffect } from 'react'
import { Text } from '@pimcore/studio-ui-bundle/components'

export const STATUS_POLL_MS = 5000

/** Feed status of an offer, refreshed every 5 s while the editor is open (the classic admin never stopped polling). */
export const OfferStatus = ({ load, text, setText }: { load: () => Promise<string>, text: string, setText: (t: string) => void }): React.JSX.Element => {
  useEffect(() => {
    let active = true
    const refresh = (): void => {
      load().then(t => { if (active) setText(t) }).catch(() => {})
    }

    refresh()
    const timer = setInterval(refresh, STATUS_POLL_MS)

    return () => {
      active = false
      clearInterval(timer)
    }
  }, [])

  return <Text style={ { alignSelf: 'center' } }>{text}</Text>
}
