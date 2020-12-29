const levenshteinDistance = function (a, b) {
  // source: https://gist.github.com/andrei-m/982927#gistcomment-1797205
  const m = []
  let i
  let j
  const min = Math.min

  if (!(a && b)) return (b || a).length

  for (i = 0; i <= b.length; m[i] = [i++]) ;
  for (j = 0; j <= a.length; m[0][j] = j++) ;

  for (i = 1; i <= b.length; i++) {
    for (j = 1; j <= a.length; j++) {
      m[i][j] = b.charAt(i - 1) === a.charAt(j - 1)
        ? m[i - 1][j - 1]
        : m[i][j] = min(
          m[i - 1][j - 1] + 1,
          min(m[i][j - 1] + 1, m[i - 1][j] + 1))
    }
  }

  return m[b.length][a.length]
}

const arraysAreEqual = function (array1, array2, compareFn = undefined) {
  if (array1.length !== array2.length) {
    return false
  }

  // sorry, CompSci degree
  const arr1 = array1.concat().sort(compareFn)
  const arr2 = array2.concat().sort(compareFn)

  for (let i = 0; i < arr1.length; i++) {
    if (arr1[i] !== arr2[i]) {
      return false
    }
  }

  return true
}

const objectsAreEqual = function (object1, object2) {
  if (typeof object1 !== 'object' || typeof object2 !== 'object') {
    return false
  }

  // loop through properties in object 1 & ensure equality
  for (const object1PropertyName in object1) {
    if (Object.prototype.hasOwnProperty.call(object1, object1PropertyName) !== Object.prototype.hasOwnProperty.call(object2, object1PropertyName)) return false

    switch (typeof (object1[object1PropertyName])) {
      case 'object':
        if (!objectsAreEqual(object1[object1PropertyName], object2[object1PropertyName])) return false
        break
      default:
        if (object1[object1PropertyName] !== object2[object1PropertyName]) return false
    }
  }

  // loop through object 2 to detect extra properties
  for (const object2PropertyName in object2) {
    if (Object.prototype.hasOwnProperty.call(object1, object2PropertyName) !== Object.prototype.hasOwnProperty.call(object2, object2PropertyName)) return false
  }

  return true
}

export { levenshteinDistance, arraysAreEqual, objectsAreEqual }
