const mapTransformer = {
  _getChildren: function (key, parentLookup) {
    if (!(key in parentLookup)) {
      return []
    }

    return parentLookup[key].map(entry => ({
      entity: entry,
      children: this._getChildren(entry['@id'], parentLookup)
    }))
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
        level
      })
      result = result.concat(...this._flattenChildren(child.children, child.entity, level + 1))
    })

    return result
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

    return this._getChildren(rootKey, parentLookup)
  },
  flatHierarchy: function (maps) {
    const hierarchy = this._hierarchy(maps)
    this._sortChildren(hierarchy)

    return this._flattenChildren(hierarchy)
  }
}

export { mapTransformer }
