# Translators
explains how parts of the application can be translated.

## structure of the application
The application, as many web applications, is divided in server-side and client-side functionality. 
This means part of the processing is done on the server, and the other part directly at the client in the browser.
Not the same technologies are used in server-side and in client-side processing, hence they need to be translated differently. 
From now on we call the client-side processing "Frontend" and the server-side processing "Server".

## Frontend
The frontend is divided into multiple small apps; each one with a specific purpose. 
These apps directly run in the browser; making editing and other actions faster to do than conventional applications, as the user does not need to reload after every action.

You can find an example by visiting /register. 
Most of what can be seen is part of the app: For example, click on the filter. It does not reload the page, but rather shows the result instantly.

All the text used in these apps can be found in the folder assets/js/localization and its subfolders.

You can deduce from the name of the folders & files where the text is going to appear in the application.
For example, if the file is named dashboard, you can access the app by visiting /dashboard.
You can find the share/craftsman view by visiting /foyer, and then clicking on one of the icon right.
You can find the share/filter view by visiting /register, and then clicking on generate link to the right. Visit this link to see it in action.

### js format
The .js files can be opened by any text editor; the encoding should be UTF-8 (normally the editor should detect this and you do not need to worry about this).
The structure should be merely obvious to understand after working with the format for a while.

To add a new language, copying the old language inside a single file, and rename the lang short to your own. Do not forget to put the `,` between the two parts!
An example is provided below, for `de` as German and `it` as Italian.
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

## Server
The server has all the data persisted, can send emails, and much more.
All the text used for these tasks can be found in the folder `translations` and its subfolders. 

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