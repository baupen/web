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
    this.constructionManagers = window.constructionManagers;
    this.maps = window.maps;
    this.craftsmen = window.craftsmen;
  }
}

export const switchStore = {
  constructionSites: null,
  constructionManagers: null,
  initializePreloaded() {
    if (!window.constructionSites) {
      return
    }

    this.constructionSites = window.constructionSites;
    this.constructionManagers = window.constructionManagers;
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
meStore.initializePreloaded()
