import { type ToolbarContext } from './types'

/**
 * Questions asked before the Optimik export, one per failed check (classic admin: checkPublished, checkQuantity).
 * The export continues only when every question is confirmed.
 */
export function optimikQuestions (warnings: ToolbarContext['optimikWarnings']): string[] {
  const questions: string[] = []

  if (warnings === undefined) return questions

  if (warnings.unpublished.length > 0) {
    questions.push(warnings.unpublished.map(key => `Product ${key} is unpublished`).join('\n') + '\n\nDo you want to proceed?')
  }

  if (warnings.quantity.length > 0) {
    questions.push(warnings.quantity
      .map(q => `${q.key}: ordered quantity ${q.quantity} is not multiplication of its serie size ${q.serieSize}`)
      .join('\n') + '\n\nDo you want to proceed?')
  }

  return questions
}
