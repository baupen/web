export const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

/**
 * A debouncing function, in spirit close to lodash.debounce.
 */
export const debounce = function (func, wait, options) {
  let lastArgs
  let lastThis
  let result
  let timerId
  let timeLastCall
  let leading = false
  let trailing = true

  wait = wait || 0
  leading = !!options?.leading
  trailing = !!options?.trailing

  function timerExpired () {
    const time = Date.now()
    const timeWaited = time - timeLastCall
    const remainingTime = wait - timeWaited

    if (remainingTime <= 0) {
      timerId = undefined

      if (trailing) {
        result = func.apply(lastThis, lastArgs)
      }
    } else {
      timerId = setTimeout(timerExpired, remainingTime)
    }
  }

  function debounced () {
    const time = Date.now()

    lastArgs = arguments
    lastThis = this
    timeLastCall = time

    if (timerId === undefined) {
      timerId = setTimeout(timerExpired, wait)

      if (leading) {
        result = func.apply(lastThis, lastArgs)
      }
    }

    return result
  }

  return debounced
}
