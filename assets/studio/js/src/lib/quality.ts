/** Colour of the product quality badge (0-100 %), same thresholds as the classic admin UI. */
export function qualityColor (quality: number): string {
  if (quality > 90) return '#35c335'
  if (quality > 50) return '#c3bc35'

  return '#c33535'
}
