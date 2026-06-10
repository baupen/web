export const store = {
  constructionSite: null,
  constructionManagers: null,
  maps: null,
  craftsmen: null,
  initializePreloaded() {
    this.constructionSite = window.constructionSite;
    this.constructionManagers = window.constructionManagers;
    this.maps = window.maps;
    this.craftsmen = window.craftsmen;
  }
}

export const meStore = {
  me: null,
  initializePreloaded() {
    this.me = window.me;
  }
}

store.initializePreloaded()
meStore.initializePreloaded()
