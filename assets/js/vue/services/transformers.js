import { iriToId } from './api'

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

const filterTransformer = {
  actualFilter: function (filter, filterActive) {
    let actualFilter = Object.assign({}, filter)
    if (!filterActive.state) {
      actualFilter.state = null
    }
    if (!filterActive.craftsmen) {
      actualFilter.craftsmen = null
    }
    if (!filterActive.maps) {
      actualFilter.maps = null
    }
    if (!filterActive.deadline) {
      actualFilter['deadline[before]'] = null
      actualFilter['deadline[after]'] = null
    }
    if (!filterActive.time) {
      actualFilter['createdAt[before]'] = null
      actualFilter['createdAt[after]'] = null
      actualFilter['registeredAt[before]'] = null
      actualFilter['registeredAt[after]'] = null
      actualFilter['resolvedAt[before]'] = null
      actualFilter['resolvedAt[after]'] = null
      actualFilter['closedAt[before]'] = null
      actualFilter['closedAt[after]'] = null
    }

    return actualFilter
  },
  filterToQuery: function (filter) {
    let query = {}

    for (const fieldName in filter) {
      if (!Object.prototype.hasOwnProperty.call(filter, fieldName)) {
        continue
      }

      const fieldValue = filter[fieldName]

      if (fieldName === 'craftsmen') {
        if (fieldValue && (fieldValue.length > 0 || fieldValue.length !== this.craftsmen.length)) {
          query['craftsman[]'] = fieldValue.map(e => iriToId(e['@id']))
        }
      } else if (fieldName === 'maps') {
        if (fieldValue && (fieldValue.length > 0 || fieldValue.length !== this.maps.length)) {
          query['map[]'] = fieldValue.map(e => iriToId(e['@id']))
        }
      } else if (fieldValue || fieldValue === false || fieldValue === 0) {
        // "false" is the only Falsy value applicable as filter
        query[fieldName] = fieldValue
      }
    }

    return query
  }
}

export { mapTransformer, filterTransformer }
