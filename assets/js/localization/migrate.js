const fs = require('fs');

fs.readdir(".", {}, function (err, filenames) {
  if (err) {
    throw new Error("failed to read directory");
  }
  console.log("found " + filenames.length + " files");

  filenames.filter(f => f !== "migrate.js" && filename.endsWith(".js")).forEach(filename => {
    parseModuleExportToJsObject(filename);
  })
});

function parseModuleExportToJsObject(filename) {
  fs.readFile(filename, "utf8", function (err, content) {
    if (err) {
      throw new Error("failed to load file " + filename);
    }
    console.log("read " + filename);

    let newContent = content.substring(content.indexOf("{"));

    const newFilename = filename.replace(".js", ".raw.js");
    fs.writeFile(newFilename, newContent, 'utf8', function (err) {
      if (err) {
        throw new Error("writing to file failed");
      }

      console.log("converted " + filename + " to js object");
      parseJsObjectToJson(newFilename);
    })
  });
}


function parseJsObjectToJson(filename) {
  const obj = require("./" + filename);
  console.log("read " + filename + " as js object");

  const newFilename = filename.replace(".raw.js", ".json");
  fs.writeFile(newFilename, JSON.stringify(obj, null, 2), (err) => {
    if (err) {
      throw new Error("writing json to file failed");
    }

    console.log("converted " + filename + " to json");
  });
}