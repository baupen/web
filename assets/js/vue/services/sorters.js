const statusProtocolEntries = ['STATUS_SET', 'STATUS_UNSET']
const statusProtocolEntryOrder = (protocolEntry) => {
  switch (protocolEntry.payload) {
    case 'CREATED':
      return 1
    case 'REGISTERED':
      return 2
    case 'RESOLVED':
      return 3
    case 'CLOSED':
      return 4
  }
}
export const sortProtocolEntries = (protocolEntries) => {
  protocolEntries.sort((a, b) => {
    if (b.createdAt === a.createdAt && statusProtocolEntries.includes(a.type) && statusProtocolEntries.includes(b.type)) {
      const aOrder = statusProtocolEntryOrder(a)
      const bOrder = statusProtocolEntryOrder(b)

      // use reversed order when the second one is unset (as then want the second one first)
      if (b.type === 'STATUS_UNSET') {
        return aOrder - bOrder
      } else {
        return bOrder - aOrder
      }
    }

    return b.createdAt.localeCompare(a.createdAt)
  })
}
