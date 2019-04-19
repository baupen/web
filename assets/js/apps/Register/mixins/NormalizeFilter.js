export default {
  methods: {
    normalizeFilter: function (filter) {
      let normalized = Object.assign({}, filter);

      const statusFilter = filter.status;
      normalized.status = Object.assign({}, statusFilter, {
        enabled: statusFilter.enabled && (statusFilter.registered || statusFilter.read || statusFilter.responded || statusFilter.reviewed)
      });

      const craftsmanFilter = filter.craftsman;
      normalized.craftsman = {
        enabled: craftsmanFilter.enabled && craftsmanFilter.craftsmen.length > 0,
        craftsmen: craftsmanFilter.craftsmen.map(i => i.id)
      };

      const tradeFilter = filter.trade;
      normalized.trade = Object.assign({}, tradeFilter, {
        enabled: tradeFilter.enabled && tradeFilter.trades.length > 0
      });

      const mapFilter = filter.map;
      normalized.map = {
        enabled: mapFilter.enabled && mapFilter.maps.length > 0,
        maps: mapFilter.maps.map(i => i.id)
      };

      const timeFilter = filter.time;
      normalized.time = Object.assign({}, timeFilter, {
        enabled: timeFilter.enabled && (timeFilter.registered.active || timeFilter.responded.active || timeFilter.reviewed.active)
      });

      return normalized;
    },
    minimizeFilter: function (filter, constructionSiteId) {
      filter = this.normalizeFilter(filter);

      let minimized = {
        constructionSiteId: constructionSiteId
      };

      if (filter.onlyMarked) {
        minimized.onlyMarked = true;
      }

      if (filter.onlyOverLimit) {
        minimized.onlyOverLimit = true;
      }

      if (filter.status.enabled) {
        minimized.status = filter.status;
      }

      if (filter.craftsman.enabled) {
        minimized.craftsman = filter.craftsman;
      }

      if (filter.trade.enabled) {
        minimized.trade = filter.trade;
      }

      if (filter.map.enabled) {
        minimized.map = filter.map;
      }

      if (filter.time.enabled) {
        minimized.time = filter.time;
      }

      return minimized;
    }
  }
};
