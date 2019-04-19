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

      let timeFilter = filter.time;
      normalized.time = Object.assign({}, timeFilter, {
        registered: this.normalizeTimeFilter(timeFilter.registered),
        responded: this.normalizeTimeFilter(timeFilter.responded),
        reviewed: this.normalizeTimeFilter(timeFilter.reviewed)
      });
      timeFilter = normalized.time;
      normalized.time.enabled = timeFilter.enabled && (timeFilter.registered.enabled || timeFilter.responded.enabled || timeFilter.reviewed.enabled);

      return normalized;
    },
    normalizeTimeFilter: function (timeFilter) {
      return Object.assign({}, timeFilter, {
        enabled: timeFilter.active && (!this.isFalsy(timeFilter.start) || !this.isFalsy(timeFilter.end))
      });
    },
    minimizeFilter: function (filter, constructionSiteId) {
      filter = this.normalizeFilter(filter);

      return Object.assign({
        constructionSiteId: constructionSiteId
      }, this.removeFalsyAndDisabled(filter));
    },
    removeFalsyAndDisabled: function (originalObject) {
      let object = Object.assign({}, originalObject);
      let disabled = false;
      Object.keys(object).forEach((key) => {
        if (key === 'enabled' && !object[key]) {
          disabled = true;
        }

        if (object[key] && typeof object[key] === 'object') {
          object[key] = this.removeFalsyAndDisabled(object[key]);
          if (Object.keys(object[key]).length === 0) {
            delete object[key];
          }
        } else if (this.isFalsy(object[key])) {
          delete object[key];
        }
      });

      if (disabled) {
        return {};
      } else {
        return object;
      }
    },
    isFalsy: function (value) {
      return value === false || value === null || value === '';
    }
  }
};
