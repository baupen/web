Api Documentation
======

**general**  
- prefix request url with ` /api`, for example `/api/login`
- there will always be an ` apiStatus` returned, if 0 then all went well.

**api errors**
````php
const SUCCESSFUL = 0;
const EMPTY_REQUEST = 1;
const UNKNOWN_IDENTIFIER = 2;
const WRONG_PASSWORD = 3;
const INVALID_AUTHENTICATION_TOKEN = 4;
const INVALID_FILE = 5;
const EXECUTION_FAILED = 6;
````
**/login**  
request
````json
{
	"identifier": "j",
	"passwordHash": "SHA256(asdf)"
}
````
response
````json
{
   "user":{
      "identifier":"j",
      "passwordHash":"f0e4c2f76c58916ec258f246851bea091d14d4247a2fc3e18694461b1816e13b",
      "plainPassword":null,
      "buildings":null,
      "authenticationToken":"WhyDJ6FZqAQHD32aSdgC",
      "markers":null,
      "id":"4023D8B2-19E1-462C-AB30-4799EDAA3C58",
      "givenName":"Sibylle",
      "familyName":"Gut",
      "fullName":"Sibylle Gut"
   },
   "apiStatus":0,
   "apiErrorMessage":null
}
````

**/authentication_status**  
request
````json
{
	"authenticationToken": "WhyDJ6FZqAQHD32aSdgC"
}
````
response
````json
{
   "apiStatus":0,
   "apiErrorMessage":null
}
````


**/file/download**  
request
````json
{
	"authenticationToken": "WhyDJ6FZqAQHD32aSdgC",
	"fileName": "image.jpg"
}
````

response  
*in binary*


**/file/upload**
request
all files with the correct filename as `multipart/form-data` request  
additionally this header:
````
mangel-authentication-token: WhyDJ6FZqAQHD32aSdgC
````

response
````json
{
   "apiStatus":0,
   "apiErrorMessage":null
}
````

**/sync**  
request
````json
{
	"authenticationToken": "WhyDJ6FZqAQHD32aSdgC",
	"markers": [
    		{
    			"markXPercentage": 0.621,
    			"markYPercentage": 0.22,
    			"frameXPercentage": 0.521,
    			"frameYPercentage": 0.07,
    			"content": "Consectetur et dolor sit.",
    			"craftsman": "2D51591E-5DAD-47BB-9705-ED6413D161A6",
    			"buildingMap": "A843A3D6-E406-4F3C-9855-D8D84529B5D1",
    			"imageFileName": "mark_image.jpg",
    			"frameXHeight": 0.2,
    			"frameYLength": 0.3,
    			"approved": null,
    			"createdBy": "03D567A8-497E-47D0-B4F6-DF620EA8D6D3",
    			"createdAt": "2018-03-12T18:01:45.347956",
    			"lastChangedAt": "2018-03-12T18:01:45.347956",
    			"fullIdentifier": "13.03.2018 08:14"
    		}
	]
}
````
if the marker is already in the db, it will be updated  
else it will be added with a new id

response  
````json
{
	"user": {
		"identifier": "j",
		"passwordHash": "f0e4c2f76c58916ec258f246851bea091d14d4247a2fc3e18694461b1816e13b",
		"plainPassword": null,
		"buildings": null,
		"authenticationToken": "rtFGRLq98hUaeMpxZA7E",
		"markers": null,
		"id": "61C78473-19B1-4AEF-8139-2457EACD6A3A",
		"givenName": "Madeleine",
		"familyName": "Hänni",
		"fullName": "Madeleine Hänni"
	},
	"craftsmen": [
		{
			"markers": null,
			"id": "2D51591E-5DAD-47BB-9705-ED6413D161A6",
			"name": "Ut facilis rerum esse.",
			"description": null,
			"phone": null,
			"email": "bernadette.blaser@jenni.com",
			"webpage": null,
			"communicationLines": [
				"bernadette.blaser@jenni.com"
			]
		}
	],
	"buildings": [
		{
			"appUsers": null,
			"buildingMaps": null,
			"id": "5E204AEC-CDD5-4729-A80A-75AB8C16B86A",
			"name": "Optio maiores aut odit nihil aut veniam ut.",
			"description": null,
			"street": "Giovanni-Zürcher-Ring 8c",
			"streetNr": "170",
			"addressLine": null,
			"postalCode": 8131,
			"city": "Avenches",
			"country": "TF",
			"addressLines": [
				"Giovanni-Zürcher-Ring 8c 170",
				8131,
				"TF"
			]
		}
	],
	"buildingMaps": [
		{
			"fileName": "1UG.pdf",
			"building": "AC809CC9-88C7-41FB-AC6F-4578242DB3C4",
			"markers": null,
			"id": "FE1BA61A-8F2A-43C7-B163-D458C78E6F96",
			"name": "1UG",
			"description": "Übersichtskarte vom 1UG"
		}
	],
	"markers": [
		{
			"markXPercentage": 0.621,
			"markYPercentage": 0.22,
			"frameXPercentage": 0.521,
			"frameYPercentage": 0.07,
			"content": "Consectetur et dolor sit.",
			"craftsman": "2D51591E-5DAD-47BB-9705-ED6413statusD161A6",
			"buildingMap": "A843A3D6-E406-4F3C-9855-D8D84529B5D1",
			"imageFileName": "mark_image.jpg",
			"frameXHeight": 0.2,
			"frameYLength": 0.3,
			"approved": null,
			"createdBy": "03D567A8-497E-47D0-B4F6-DF620EA8D6D3",
			"id": "FAD29C44-D515-4A20-A689-0CCAA1246588",
			"createdAt": "2018-03-12T18:01:45.347956",
			"lastChangedAt": "2018-03-12T18:01:45.347956",
			"fullIdentifier": "13.03.2018 08:14"
		}
	],
	"apiStatus": 0,
	"apiErrorMessage": null
}
````