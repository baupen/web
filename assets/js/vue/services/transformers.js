import { iriToId } from './api'
import XLSX from 'xlsx'

const issueTransformer = {
  isOverdue: function (issue) {
    if (!issue.deadline || issue.resolvedBy || issue.closedBy) {
      return false
    }

    const deadline = Date.parse(issue.deadline)
    const now = Date.now()
    return deadline < now
  }
}

const excelTransformer = {
  mimeTypeXlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  mimeTypeXls: 'application/vnd.ms-excel',
  getImportMimeTypes: function () {
    return [this.mimeTypeXlsx, this.mimeTypeXls]
  },
  exportToXlsx: function (content, header, worksheetName) {
    const workbook = XLSX.utils.book_new()
    const worksheet = XLSX.utils.aoa_to_sheet([header, ...content])
    XLSX.utils.book_append_sheet(workbook, worksheet, worksheetName)

    const blobPart = XLSX.write(workbook, {
      bookType: 'xlsx',
      bookSST: false,
      type: 'array'
    })

    return new Blob([blobPart], { type: this.mimeTypeXlsx })
  },
  import: function (arrayBuffer) {
    const data = new Uint8Array(arrayBuffer)
    const workbook = XLSX.read(data, { type: 'array' })
    const worksheet = workbook.Sheets[workbook.SheetNames[0]]

    return XLSX.utils.sheet_to_json(worksheet, { header: 1 })
  }
}

const craftsmanTransformer = {
  tableHeader: ['trade', 'company', 'contact_name', 'email', 'emailCCs'],
  defaultContent: [
    ['Support & Requirements', 'baupen.ch', 'Adrian Hoffman', 'adrian.hoffman@baupen.ch', 'info@baupen.ch, support@baupen.ch'],
    ['Web', 'baupen.ch', 'Florian Moser', 'florian.moser@baupen.ch'],
    ['iOS', 'baupen.ch', 'Julian Dunskus', 'julian.dunskus@baupen.ch']
  ],
  _xlsxHeader: function (translator) {
    const translatedHeader = this.tableHeader.map(h => translator('craftsman.' + h))
    translatedHeader[translatedHeader.length - 1] += ' (' + translator('craftsman._excel_format_emailCCs') + ')'

    return translatedHeader
  },
  _createXlsx: function (content, translator) {
    const translatedHeader = this._xlsxHeader(translator)
    const worksheetName = translator('craftsman._plural')

    return excelTransformer.exportToXlsx(content, translatedHeader, worksheetName)
  },
  exportToXlsx: function (craftsmen, translator) {
    const content = craftsmen.map(c => [c.trade, c.company, c.contactName, c.email, c.emailCCs.join(', ')])

    return this._createXlsx(content, translator)
  },
  importExcelTemplate: function (translator) {
    return this._createXlsx(this.defaultContent, translator)
  },
  importExcel: function (arrayBuffer) {
    const content = excelTransformer.import(arrayBuffer)

    const craftsmen = []
    for (let i = 1; i < content.length; i++) {
      const entry = content[i]

      const craftsman = {
        trade: entry[0],
        company: entry[1],
        contactName: entry[2],
        email: entry[3],
        emailCCs: []
      }

      if (entry[4]) {
        craftsman.emailCCs = entry[4].split(',').map(e => e.trim()).filter(e => e)
      }

      craftsmen.push(craftsman)
    }

    return craftsmen
  }
}

const treeTransformer = {
  /*
   the data structure inside this transformer is a "tree".
   a "tree" is a list of entries; each entry looks like {entity, children:[tree, tree, ...]}
   */
  _createParentLookup: function (defaultParent, entities, parentResolveFunc) {
    const parentLookup = {}

    entities.forEach(entity => {
      const parentKey = parentResolveFunc(entity) ?? defaultParent

      if (!parentLookup[parentKey]) {
        parentLookup[parentKey] = []
      }

      parentLookup[parentKey].push(entity)
    })

    return parentLookup
  },
  _create: function (key, parentLookup) {
    if (!(key in parentLookup)) {
      return []
    }

    const children = parentLookup[key].map(entry => ({
      entity: entry,
      children: this._create(entry['@id'], parentLookup)
    }))

    // remove processed entries in lookup
    delete parentLookup[key]

    return children
  },
  _createWithReminders: function (rootKey, parentLookup) {
    const tree = this._create(rootKey, parentLookup)

    // append any entries that remain. this case should never happen (mean broken relations!)
    for (const key in parentLookup) {
      if (Object.prototype.hasOwnProperty.call(parentLookup, key)) {
        tree.push(...parentLookup[key].map(entry => ({
          entity: entry,
          children: []
        })))
      }
    }

    return tree
  },
  _sortInPlace: function (tree, sortFunc) {
    tree.sort((a, b) => sortFunc(a, b))
    tree.forEach(child => {
      this._sortInPlace(child.children, sortFunc)
    })
  },
  _addLevelInPlace: function (tree, level = 0) {
    tree.forEach(child => {
      child.level = level
      this._addLevelInPlace(child.children, level + 1)
    })
  },
  _addParentInPlace: function (tree, parent = null) {
    tree.forEach(child => {
      child.parent = parent
      this._addParentInPlace(child.children, child)
    })
  },
  _addParentsInPlace: function (tree, parents = []) {
    tree.forEach(child => {
      child.parents = parents
      const newParents = [...parents, child]
      this._addParentsInPlace(child.children, newParents)
    })
  },
  _addSiblingsInPlace: function (tree) {
    tree.forEach(child => {
      child.siblings = tree.filter(t => t !== child)
      this._addSiblingsInPlace(child.children)
    })
  },
  _traverseDepthFirst: function (tree, func) {
    tree.forEach(child => {
      this._traverseDepthFirst(child.children, func)
      func(child)
    })
  },
  _addPropertyInPlace: function (tree, property, propertyResolveFunc) {
    this._traverseDepthFirst(tree, child => { child[property] = propertyResolveFunc(child) })
  },
  _flattenToList: function (tree) {
    let result = []
    tree.forEach(child => {
      result.push(child)
      result = result.concat(...this._flattenToList(child.children))
    })

    return result
  },
  _flattenToLookup: function (tree, keyFunc) {
    const lookup = {}
    tree.forEach(child => {
      const key = keyFunc(child)
      lookup[key] = child

      const recursiveLookup = this._flattenToLookup(child.children, keyFunc)
      Object.assign(lookup, recursiveLookup)
    })

    return lookup
  }
}

const mapTransformer = {
  PROPERTY_LEVEL: 1,
  PROPERTY_PARENT: 2,
  PROPERTY_PARENTS: 4,
  PROPERTY_SIBLINGS: 8,
  PROPERTY_MAP_PARENT_NAMES: 16,
  PROPERTY_HAS_CHILD_WITH_ISSUES: 32,
  PROPERTY_ISSUE_SUM_WITH_CHILDREN: 64,
  _createMapTree: function (maps) {
    const noParentKey = 'root'
    const parentLookup = treeTransformer._createParentLookup(noParentKey, maps, m => m.parent)
    return treeTransformer._createWithReminders(noParentKey, parentLookup)
  },
  _createSortedMapTree: function (maps) {
    const tree = this._createMapTree(maps)

    treeTransformer._sortInPlace(tree, (a, b) => a.entity.name.localeCompare(b.entity.name))

    return tree
  },
  _addPropertiesInPlace: function (tree, properties = 0) {
    // noinspection JSBitwiseOperatorUsage
    if (properties & this.PROPERTY_LEVEL) {
      treeTransformer._addLevelInPlace(tree)
    }
    if (properties & this.PROPERTY_PARENT) {
      treeTransformer._addParentInPlace(tree)
    }
    if (properties & this.PROPERTY_PARENTS) {
      treeTransformer._addParentsInPlace(tree)
    }
    if (properties & this.PROPERTY_SIBLINGS) {
      treeTransformer._addSiblingsInPlace(tree)
    }
    if (properties & this.PROPERTY_MAP_PARENT_NAMES) {
      if (!(properties & this.PROPERTY_PARENTS)) {
        treeTransformer._addParentsInPlace(tree)
      }
      const mapParentNamesFunc = node => node.parents.map(p => p.entity.name)
      treeTransformer._addPropertyInPlace(tree, 'mapParentNames', mapParentNamesFunc)
    }
    if (properties & this.PROPERTY_HAS_CHILD_WITH_ISSUES) {
      const hasChildWithIssuesFunc = child => child.children.some(c => c.issueCount > 0 || c.hasChildWithIssues)
      treeTransformer._addPropertyInPlace(tree, 'hasChildWithIssues', hasChildWithIssuesFunc)
    }
    if (properties & this.PROPERTY_ISSUE_SUM_WITH_CHILDREN) {
      const issueSumWithChildrenFunc = child => child.issueCount + child.children.reduce((acc, curr) => acc + curr.issueSumWithChildren, 0)
      treeTransformer._addPropertyInPlace(tree, 'issueSumWithChildren', issueSumWithChildrenFunc)
    }
  },
  _flattenToListWithProperties: function (tree, properties = 0) {
    this._addPropertiesInPlace(tree, properties)

    return treeTransformer._flattenToList(tree)
  },
  _flattenToLookupWithProperties: function (tree, properties = 0) {
    this._addPropertiesInPlace(tree, properties)

    return treeTransformer._flattenToLookup(tree, node => node.entity['@id'])
  },
  _addIssueGroupsInPlace: function (tree, mapGroups) {
    const mapGroupLookup = {}
    mapGroups.forEach(mg => { mapGroupLookup[mg.entity] = mg })

    treeTransformer._traverseDepthFirst(tree, node => {
      const mapGroup = mapGroupLookup[node.entity['@id']]
      node.issueCount = mapGroup ? mapGroup.count : 0
      node.earliestDeadline = mapGroup && mapGroup.earliestDeadline ? mapGroup.earliestDeadline : null
    })
  },
  orderedList: function (maps, properties = 0) {
    const tree = this._createSortedMapTree(maps)

    return this._flattenToListWithProperties(tree, properties)
  },
  orderedListWithIssuesGroups: function (maps, issueGroupsByMap, properties = 0) {
    const tree = this._createSortedMapTree(maps)

    this._addIssueGroupsInPlace(tree, issueGroupsByMap)

    return this._flattenToListWithProperties(tree, properties)
  },
  lookup: function (maps, properties) {
    const tree = this._createMapTree(maps)

    return this._flattenToLookupWithProperties(tree, properties)
  },
  groupByIssueCount: function (maps, mapGroups, maxCount) {
    const tree = this._createMapTree(maps)

    this._addIssueGroupsInPlace(tree, mapGroups)
    const properties = this.PROPERTY_PARENT | this.PROPERTY_PARENTS | this.PROPERTY_SIBLINGS | this.PROPERTY_ISSUE_SUM_WITH_CHILDREN
    this._addPropertiesInPlace(tree, properties)

    let notIncludedMaps = treeTransformer._flattenToList(tree)

    const sortDesc = (a, b) => b.issueSumWithChildren - a.issueSumWithChildren
    const sortAsc = (a, b) => a.issueSumWithChildren - b.issueSumWithChildren
    const groups = []
    while (true) {
      const shouldIncludeMaps = notIncludedMaps.filter(m => m.issueSumWithChildren > 0)
      if (shouldIncludeMaps.length === 0) {
        break
      }

      // try to find map under the specified limit
      const mapsUnderLimit = shouldIncludeMaps.filter(m => m.issueSumWithChildren <= maxCount)
      const mapsUnderLimitExist = mapsUnderLimit.length
      const initialChosenMap = mapsUnderLimitExist ? mapsUnderLimit.sort(sortDesc)[0] : shouldIncludeMaps.sort(sortAsc)[0]

      const chosenMaps = [initialChosenMap]
      let chosenIssueSum = initialChosenMap.issueSumWithChildren

      // try to combine with siblings
      if (mapsUnderLimitExist) {
        initialChosenMap.siblings.sort(sortDesc)
        initialChosenMap.siblings.forEach(sibling => {
          if (sibling.issueSumWithChildren + chosenIssueSum <= maxCount) {
            chosenMaps.push(sibling)
            chosenIssueSum += sibling.issueSumWithChildren
          }
        })
      }

      // create group with chosen maps & their children (already included in chosenIssueSum)
      let group = [...chosenMaps]
      chosenMaps.forEach(chosenMap => {
        group = group.concat(...treeTransformer._flattenToList(chosenMap.children))
      })
      groups.push({
        groupIssueSum: chosenIssueSum,
        group
      })

      // remove chosen maps from the data structure
      notIncludedMaps = notIncludedMaps.filter(m => !group.includes(m))
      chosenMaps.forEach(chosenMap => {
        if (chosenMap.parent) {
          chosenMap.siblings.forEach(s => { s.siblings = s.siblings.filter(e => e !== chosenMap) })
          chosenMap.parents.forEach(p => { p.issueSumWithChildren -= chosenMap.issueSumWithChildren })
          chosenMap.parent.children = chosenMap.parent.children.filter(c => c !== chosenMap)
        }
      })
    }

    return groups
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
    const query = Object.assign({}, defaultFilter)

    if (!filter) {
      return query
    }

    const textProps = ['number', 'description']
    textProps.filter(p => filter[p])
      .forEach(p => { query[p] = filter[p] })

    const booleanProps = ['isMarked', 'wasAddedWithClient']
    booleanProps.filter(p => filter[p] || filter[p] === false)
      .forEach(p => { query[p] = filter[p] })

    if (!configuration) {
      return query
    }

    if (configuration.state) {
      query.state = filter.state
    }
    if (configuration.craftsmen && this.shouldIncludeCollection(filter.craftsmen, craftsmen)) {
      query['craftsman[]'] = filter.craftsmen.map(e => iriToId(e['@id']))
    }
    if (configuration.maps && this.shouldIncludeCollection(filter.maps, maps)) {
      query['map[]'] = filter.maps.map(e => iriToId(e['@id']))
    }

    const whitelistDateTimePropNames = []
    if (configuration.deadline) {
      whitelistDateTimePropNames.push('deadline')
    }
    if (configuration.time) {
      whitelistDateTimePropNames.push('createdAt', 'registeredAt', 'resolvedAt', 'closedAt')
    }
    const whitelistDateTimeProps = []
    whitelistDateTimePropNames.forEach(prop => {
      whitelistDateTimeProps.push(prop + '[before]')
      whitelistDateTimeProps.push(prop + '[after]')
    })
    whitelistDateTimeProps.filter(p => filter[p])
      .forEach(p => { query[p] = filter[p] })

    return query
  },
  filterToFilterEntity: function (query, constructionSite) {
    const filter = { constructionSite: constructionSite['@id'] }

    if (!query) {
      return filter
    }

    for (const fieldName in query) {
      if (Object.prototype.hasOwnProperty.call(query, fieldName)) {
        const value = query[fieldName]
        const beforeIndex = fieldName.indexOf('[before]')
        const afterIndex = fieldName.indexOf('[after]')
        if (beforeIndex > 0) {
          filter[fieldName.substr(0, beforeIndex) + 'Before'] = value
        } else if (afterIndex > 0) {
          filter[fieldName.substr(0, afterIndex) + 'After'] = value
        } else if (fieldName === 'craftsman[]') {
          filter.craftsmanIds = value
        } else if (fieldName === 'map[]') {
          filter.mapIds = value
        } else if (fieldName === 'number[]') {
          filter.numbers = value
        } else if (fieldName === 'number') {
          filter.numbers = [value]
        } else {
          filter[fieldName] = value
        }
      }
    }

    return filter
  },
  filterEntityToFilter: function (entity) {
    const filter = {}
    for (const fieldName in entity) {
      if (Object.prototype.hasOwnProperty.call(entity, fieldName)) {
        const value = entity[fieldName]
        if (value === null) {
          continue
        }

        const beforeIndex = fieldName.indexOf('Before')
        const afterIndex = fieldName.indexOf('After')
        if (beforeIndex > 0) {
          filter[fieldName.substr(0, beforeIndex) + '[before]'] = value
        } else if (afterIndex > 0) {
          filter[fieldName.substr(0, afterIndex) + '[after]'] = value
        } else if (fieldName === 'craftsmanIds') {
          filter['craftsman[]'] = value
        } else if (fieldName === 'mapIds') {
          filter['map[]'] = value
        } else if (fieldName === 'numbers') {
          filter['number[]'] = value
        } else if (fieldName === 'constructionSite' || fieldName === 'filteredUrl' || fieldName === 'authenticationToken' || fieldName.startsWith('@')) {
        } else {
          filter[fieldName] = value
        }
      }
    }

    return filter
  }
}

export { issueTransformer, mapTransformer, filterTransformer, craftsmanTransformer, excelTransformer }
