import { createEntityIdLookup } from './algorithms'

const mapTransformer = {
  _cutChildrenFromLookup: function (key, parentLookup) {
    if (!(key in parentLookup)) {
      return []
    }

    const children = parentLookup[key].map(entry => ({
      entity: entry,
      children: this._cutChildrenFromLookup(entry['@id'], parentLookup)
    }))

    // remove processed entries in lookup
    delete parentLookup[key]

    return children
  },
  _sortChildren: function (children) {
    children.sort((a, b) => a.entity.name.localeCompare(b.entity.name))
    children.forEach(child => {
      this._sortChildren(child.children)
    })
  },
  _flattenChildren: function (children, parent = null, level = 0) {
    let result = []
    children.forEach(child => {
      result.push({
        entity: child.entity,
        parent,
        children: child.children.map(e => e.entity),
        level
      })
      result = result.concat(...this._flattenChildren(child.children, child.entity, level + 1))
    })

    return result
  },
  _childrenLookup: function (lookup, children, parents = []) {
    children.forEach(child => {
      lookup[child.entity['@id']] = parents
      const newParents = [...parents, child.entity]
      this._childrenLookup(lookup, child.children, newParents)
    })
  },
  _hierarchy: function (maps) {
    const rootKey = 'root'

    const parentLookup = {}
    maps.forEach(m => {
      const parentKey = m.parent ?? rootKey

      if (!parentLookup[parentKey]) {
        parentLookup[parentKey] = []
      }

      parentLookup[parentKey].push(m)
    })

    const children = this._cutChildrenFromLookup(rootKey, parentLookup)

    // append any entries that remain. this case should never happen (mean broken relations!)
    for (const key in parentLookup) {
      if (Object.prototype.hasOwnProperty.call(parentLookup, key)) {
        children.push(...parentLookup[key].map(entry => ({
          entity: entry,
          children: []
        })))
      }
    }

    return children
  },
  parentsLookup: function (maps) {
    const hierarchy = this._hierarchy(maps)

    const lookup = {}
    this._childrenLookup(lookup, hierarchy)

    return lookup
  },
  flatHierarchy: function (maps) {
    const hierarchy = this._hierarchy(maps)
    this._sortChildren(hierarchy)

    return this._flattenChildren(hierarchy)
  }
}

export { mapTransformer }
