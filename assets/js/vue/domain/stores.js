export const store = {
  constructionSite: null,
  constructionManagers: null,
  maps: null,
  craftsmen: null,
  initializePreloaded() {
    if (!window.constructionSite) {
      return
    }

    this.constructionSite = window.constructionSite;
    this.constructionManagers = window.constructionManagers['hydra:member'];
    this.maps = window.maps['hydra:member'];
    this.craftsmen = window.craftsmen['hydra:member'];
  }
}

export const switchStore = {
  constructionSites: null,
  constructionManagers: null,
  initializePreloaded() {
    if (!window.constructionSites) {
      return
    }

    this.constructionSites = window.constructionSites['hydra:member'];
    this.constructionManagers = window.constructionManagers['hydra:member'];
  }
}

export const meStore = {
  me: null,
  initializePreloaded() {
    if (!window.me) {
      return
    }

    this.me = window.me;
  }
}

store.initializePreloaded()
switchStore.initializePreloaded()
meStore.initializePreloaded()
