# Translators
explains how parts of the application can be translated.

## structure of the application
The application, as many web applications, is divided in server-side and client-side functionality. 
This means part of the processing is done on the server, and the other part directly at the client in the browser.
This is important to know, because not the same technologies are used in server-side and in client-side processing.
From now on we call the client-side processing "Apps" and the server-side processing "Application". 
These two parts of mangel.io are translated differently. 


## Apps
An app makes editing and other actions faster to do than conventional applications, as the user does not need to reload after every action.
You can find an example by visiting /register. Most of what can be seen is part of the app: Clicking on the filter does not navigate, but rather shows the result instantly.
All the text used in these apps can be found in the folder assets/js/localization and its subfolders.

You can deduce from the name of the folders & files where the text is going to appear in the application.
If the file is named dashboard, you can access the app by visiting /dashboard.
You can find the share/craftsman view by visiting /foyer, and then clicking on one of the icon right.
You can find the share/filter view by visiting /register, and then clicking on generate link to the right. Visit this link to see it in action.

### js format
The .js files can be opened by any text editor; the encoding should be UTF-8 (normally the editor should detect this and you do not need to worry about this).
The content is structured using `{`, `:`, `"` and `,`. The structure should be merely obvious to understand after working with the format for a while.

To add a new language, create a new area, copying the old language. Do not forget to put the `,` between the two parts!
An example is provided below, for `de` as German and `it` as Italien.
Never change the keys (the left part), only change the text (inside the double quotes). 
If you need to use a double quote in your text, write instead `\"`. So for example `my_example: "this has a \" in it"`.

### example

```js
export default {
    de: {
        register: {
            name: "Pendenzen",
            status_actions: {
                open: "Offen",
                overdue: "Frist überschritten",
                to_inspect: "Zur Inspektion",
                marked: "Markiert"
				}
        }
	},
    it: {
        register: {
            name: "Pendenzen",
            status_actions: {
                open: "Offen",
                overdue: "Frist überschritten",
                to_inspect: "Zur Inspektion",
                marked: "Markiert"
				}
        }
	}
}
```

## Application
The application processing client-side requests (like saving something), sends emails, and much more.
All the text used in the application can be found in the folder `translations` and its subfolders. 

### yml files
The .yml files can be opened by any text editor; the encoding should be UTF-8 (normally the editor should detect this and you do not need to worry about this).
The content is structured using tabs, so it relays on correct intendation.

To add a new language, copy the file from an existing language and replace the language short with the new one.
For example, copy the file "dashboard.de.yml" to "dashboard.it.yml". Then translate the content of "dashboard.it.yml" to Italien.
Never change the keys (the left part), only change the text (inside the single quotes). 
If you need to use a single quote (or apostraph) in your text, write instead `\'`. So for example `my_example: 'this has a \' in it'`.

```yml
dashboard:
  title: 'Dashboard'
  description: 'Überblick über Ihre Baustelle'
```