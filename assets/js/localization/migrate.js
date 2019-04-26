const fs = require('fs');

import dashboardDe from './dashboard.de';

parseJsObjectToJson(dashboardDe);

function parseJsObjectToJson(object, name) {
  fs.writeFile(name + ".json", JSON.stringify(obj, null, 2), (err) => {
    if (err) {
      throw new Error("writing json to file failed");
    }

    console.log("converted " + filename + " to json");
  });
}