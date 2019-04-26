const fs = require('fs');


fs.readdir(".", {}, function (err, files) {
  if (err) {
    throw new Error("failed to read directory");
  }
  console.log("found " + files.length + " files");

  files.filter(f => f !== "migrate.js").forEach(file => {
    parseModuleExportToJsObject(file);
  })
});

function parseModuleExportToJsObject(file) {
  fs.readFile(file, "utf8", function (err, content) {
    if (err) {
      throw new Error("failed to load file");
    }
    console.log("read " + file);

    let newContent = content.substring(content.indexOf("{"));

    fs.writeFile(file, newContent, 'utf8', function (err) {
      if (err) {
        throw new Error("writing to file failed");
      }

      console.log("converted " + file + " to js object");
      parseJsObjectToJson(file);
    })
  });
}


function parseJsObjectToJson(file) {
  const obj = require("./" + file);
  console.log("read " + file + " as js object");
  fs.writeFile("dashboard.de.json", JSON.stringify(obj, null, 2), (err) => {
    if (err) {
      throw new Error("writing json to file failed");
    }

    console.log("converted " + file + " to json");
  });
}