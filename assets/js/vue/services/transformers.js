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
  defaultFilter: function (view) {
    return {
      isDeleted: false,
      state: view === 'foyer' ? 1 : 14 // 14 = 8 | 4 | 2
    }
  },
  defaultConfiguration: function (view) {
    return {
      showState: view === 'register',
      state: false,
      craftsmen: false,
      maps: false,
      deadline: false,
      time: false
    }
  },
  actualFilter: function (filter, configuration) {
    let whitelistProps = ['number', 'description', 'isMarked', 'wasAddedWithClient']
    let whitelistDateTimeProps = []
    if (configuration.state) {
      whitelistProps.push('state')
    }
    if (configuration.craftsmen) {
      whitelistProps.push('craftsmen')
    }
    if (configuration.maps) {
      whitelistProps.push('maps')
    }
    if (configuration.deadline) {
      whitelistDateTimeProps.push('deadline')
    }
    if (configuration.time) {
      whitelistDateTimeProps.push('createdAt', 'registeredAt', 'resolvedAt', 'closedAt')
    }

    let actualFilter = {}
    whitelistProps.forEach(prop => {
      actualFilter[prop] = filter[prop]
    })
    whitelistDateTimeProps.forEach(prop => {
      actualFilter[prop + '[before]'] = filter[prop + '[before]']
      actualFilter[prop + '[after]'] = filter[prop + '[after]']
    })

    return actualFilter
  },
  filterToQuery: function (filter, craftsmen, maps) {
    let query = {}

    for (const fieldName in filter) {
      if (!Object.prototype.hasOwnProperty.call(filter, fieldName)) {
        continue
      }

      const fieldValue = filter[fieldName]

      if (fieldName === 'craftsmen') {
        if (fieldValue && (fieldValue.length > 0 || fieldValue.length !== craftsmen.length)) {
          query['craftsman[]'] = fieldValue.map(e => iriToId(e['@id']))
        }
      } else if (fieldName === 'maps') {
        if (fieldValue && (fieldValue.length > 0 || fieldValue.length !== maps.length)) {
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
