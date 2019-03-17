export default {
  de: {
    register: {
      name: 'Pendenzen',
      status_actions: {
        open: 'Offen',
        overdue: 'Frist überschritten',
        to_inspect: 'Zur Inspektion',
        marked: 'Markiert'
      }
    },
    dialog: {
      new_issues_in_foyer: 'Eine neue Pendenz | {count} neue Pendenzen',
      add_to_register: 'Jetzt zum Verzeichnis hinzufügen'
    },
    feed: {
      name: 'Feed',
      show_more: 'Mehr anzeigen',
      no_entries_yet: 'Keine Aktivitäten auf der Baustelle zur Zeit. Fügen Sie Pendenzen hinzu, damit hier etwas angezeigt wird.',
      entries: {
        response_received: '{craftsman} hat eine Pendenz beantwortet. | {craftsman} hat {count} Pendenzen beantwortet.',
        visited_webpage: '{craftsman} hat die Pendenzen angeschaut.',
        overdue_limit: '{craftsman} hat die Frist vom %limit% bei {count} Pendenzen überschritten.'
      }
    },
    notes: {
      name: 'Notizen',
      no_entries_yet: 'Noch keine Notizen. Notieren Sie sich hier, was Sie ungern vergessen möchten.',
      actions: {
        add_new: 'Neu erfassen'
      }
    }
  },
  it: {
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
  }
};
