export const PUBLIC = 'PUBLIC';
export const PRIVATE = 'PRIVATE';
export const HIDDEN = 'HIDDEN';

/**
 *
 * @param {String} value
 * @return {Boolean}
 */
export function isPublic(value) {
  return value === PUBLIC;
}

/**
 *
 * @param {String} value
 * @return {Boolean}
 */
export function isPrivate(value) {
  return value === PRIVATE;
}

/**
 *
 * @param {String} value
 * @return {boolean}
 */
export function isHidden(value) {
  return value === HIDDEN;
}
