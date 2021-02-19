import { iriToId } from './api'

const issueTransformer = {
  isOverdue: function (issue) {
    if (!issue.deadline || issue.responseBy || issue.closedBy) {
      return false;
    }

    const deadline = Date.parse(issue.deadline)
    const now = Date.now()
    return deadline < now
  }
}

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
  shouldIncludeCollection: function (value, collection) {
    return value && (value.length > 0 || value.length !== collection.length)
  },
  filterToQuery: function (defaultFilter, filter, configuration, craftsmen, maps) {
    let query = Object.assign({}, defaultFilter)

    if (!filter) {
      return query;
    }

    const textProps = ['number', 'description']
    textProps.filter(p => filter[p]).forEach(p => query[p] = filter[p])

    const booleanProps = ['isMarked', 'wasAddedWithClient']
    booleanProps.filter(p => filter[p] || filter[p] === false).forEach(p => query[p] = filter[p])

    if (!configuration) {
      return query;
    }

    if (configuration.state) {
      query['state'] = filter['state']
    }
    if (configuration.craftsmen && this.shouldIncludeCollection(filter['craftsmen'], craftsmen)) {
      query['craftsman[]'] = filter['craftsmen'].map(e => iriToId(e['@id']))
    }
    if (configuration.maps && this.shouldIncludeCollection(filter['maps'], maps)) {
      query['maps[]'] = filter['maps'].map(e => iriToId(e['@id']))
    }

    let whitelistDateTimePropNames = []
    if (configuration.deadline) {
      whitelistDateTimePropNames.push('deadline')
    }
    if (configuration.time) {
      whitelistDateTimePropNames.push('createdAt', 'registeredAt', 'resolvedAt', 'closedAt')
    }
    let whitelistDateTimeProps = []
    whitelistDateTimePropNames.forEach(prop => {
      whitelistDateTimeProps.push(prop + '[before]')
      whitelistDateTimeProps.push(prop + '[after]')
    })
    whitelistDateTimeProps.filter(p => filter[p]).forEach(p => query[p] = filter[p])

    return query
  }
}

export { issueTransformer, mapTransformer, filterTransformer }
