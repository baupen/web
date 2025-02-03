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
export const orderIssueEvents = (issueEvents) => {
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

export const filterIssueEventsForIssue = (orderedIssueEvents, issueId) => {
  let isOpen = false
  const keptIssueEvents = []
  for (let i = orderedIssueEvents.length - 1; i >= 0; i--) {
    const issueEvent = orderedIssueEvents[i]
    if (issueId.includes(issueEvent.root) || isOpen) {
      keptIssueEvents.push(issueEvent)
    }

    if (issueId.includes(issueEvent.root)) {
      // only after "registration" does the issue exist to the craftsman/constructionSite
      if (issueEvent.type === 'STATUS_SET' && issueEvent.payload === 'REGISTERED') {
        isOpen = true
      }

      // issue is closed, hence no longer exists
      if (issueEvent.type === 'STATUS_SET' && issueEvent.payload === 'CLOSED') {
        isOpen = false
      }

      // issue is reopened, hence again exists
      if (issueEvent.type === 'STATUS_UNSET' && issueEvent.payload === 'CLOSED') {
        isOpen = true
      }
    }
  }

  keptIssueEvents.reverse()

  return keptIssueEvents
}
