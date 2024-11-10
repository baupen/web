const statusIssueEvents = ['STATUS_SET', 'STATUS_UNSET']
const statusIssueEventOrder = (issueEvent) => {
  switch (issueEvent.payload) {
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
export const sortIssueEvents = (issueEvents) => {
  issueEvents.sort((a, b) => {
    if (b.timestamp === a.timestamp && statusIssueEvents.includes(a.type) && statusIssueEvents.includes(b.type)) {
      const aOrder = statusIssueEventOrder(a)
      const bOrder = statusIssueEventOrder(b)

      // use reversed order when the second one is unset (as then want the second one first)
      if (b.type === 'STATUS_UNSET') {
        return aOrder - bOrder
      } else {
        return bOrder - aOrder
      }
    }

    return b.timestamp.localeCompare(a.timestamp)
  })
}
