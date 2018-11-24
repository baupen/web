export default {
    methods: {
        normalizeFilter: function (filter) {
            let normalized = {};
            normalized.constructionSiteId = filter.constructionSiteId;

            if (filter.issue.enabled) {
                normalized.issue = {enabled: true, issues: filter.issue.issues.map(i => i.id) };
            }
            if (filter.status.enabled) {
                normalized.status = {enabled: true, registered: filter.status.registered, read: filter.status.read, responded: filter.status.responded, reviewed: filter.status.reviewed };
            }
            if (filter.craftsman.enabled) {
                normalized.craftsman = {enabled: true, craftsmen: filter.craftsman.craftsmen.map(i => i.id) };
            }
            if (filter.trade.enabled) {
                normalized.trade = {enabled: true, craftsmen: filter.trade.trades };
            }
            if (filter.map.enabled) {
                normalized.map = {enabled: true, map: filter.map.maps.map(m => m.id) };
            }
            if (filter.time.enabled) {
                const time = filter.time;
                let nTime = {enabled: true};

                if (time.read.active) {
                    nTime.read = {active: true};
                }
                if (filter.time.registered.active) {
                    nTime.registered = {active: true, start: time.registered.start, end: time.registered.end};
                }
                if (filter.time.responded.active) {
                    nTime.responded = {active: true, start: time.responded.start, end: time.responded.end};
                }
                if (filter.time.reviewed.active) {
                    nTime.reviewed = {active: true, start: time.reviewed.start, end: time.reviewed.end};
                }

                normalized.time = nTime;
            }

            normalized.onlyMarked = filter.onlyMarked;
            normalized.onlyOverLimit= filter.onlyOverLimit;
            normalized.numberText = filter.numberText;

            return normalized;
        }
    }
};