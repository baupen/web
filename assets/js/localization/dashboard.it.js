export default {
  register: {
    name: 'Pendenze',
    status_actions: {
      open: 'Aperte',
      overdue: 'Termine superato',
      to_inspect: 'Da ispezionare',
      marked: 'Contrassegnate'
    }
  },
  dialog: {
    new_issues_in_foyer: 'Una nuova pendenza | {count} nuove pendenze',
    add_to_register: 'Aggiungere al registro'
  },
  feed: {
    name: 'Feed',
    show_more: 'Mostrare di più',
    entries: {
      response_received: '{craftsman} ha risposto ad una pendenza. | {craftsman} ha risposto a {count} pendenze.',
      visited_webpage: '{craftsman} ha visualizzato le pendenze.',
      overdue_limit: '{craftsman} ha superato il termine di {count} pendenze di %limit%.'
    }
  },
  notes: {
    name: 'Note',
    actions: {
      add_new: 'Aggiungi'
    }
  }
};
