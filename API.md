# General
* Base URL is /api, e.g. /login would be /api/login.
* Responses are always Response objects.
* The HTTP status code indicates if a request completed successfully
	* e.g. file download requests use this to decide whether to view the response as binary or JSON
* All values are never null unless their type is annotated with `?`, e.g. `String?`.
* Strings being null generally indicates that the user hasn't had the chance to enter it yet.
* UUIDs are taken over from objects client sends (request fails if UUID makes no sense, e.g. duplicate ID, invalid reference)
* Filenames are UUIDs (with the corresponding extension), e.g. `D366E820-6EF1-4FDE-8B78-7B8D696D4C05.jpg`
* Changing contents necessitates changing filename.
* Clients build up a backlog queue of requests to send whilst not connected to the internet.
	* backlog has to be sent in order
	* Update is only sent after backlog has been cleared.



# Methods


## `/trial`
* creates a new account with some sample data, as a trial, returning the credentials.

##### request:
empty

##### response:
```
username: String
password: String
```


## `/login`
* returned token should be kept alive for as long as possible, even supporting multiple logged in devices

##### request:
```
username: String
passwordHash: SHA256
```

##### response:
```
user: User
```


## `/read`
* used to update all changed objects
* request contains meta objects expressing the client's current knowledge

##### request:
```
authenticationToken: String
user: ObjectMeta
craftsmen: [ObjectMeta]
constructionSites: [ObjectMeta]
maps: [ObjectMeta]
issues: [ObjectMeta]
```

##### response:
```
changedCraftsmen: [Craftsman]
removedCraftsmanIDs: [UUID]
changedConstructionSites: [ConstructionSite]
removedConstructionSiteIDs: [UUID]
changedMaps: [Map]
removedMapIDs: [UUID]
changedIssues: [Issue]
removedissueIDs: [UUID]
changedUser: User?
```


## `/file/download`
* used to download file data

##### request:
* `constructionSite` xor `map` xor `issue` has to be non-null
```
authenticationToken: String
constructionSite: ObjectMeta?
map: ObjectMeta?
issue: ObjectMeta?
```

##### response:
file contents or json fail response if invalid


## `/issue/create`
* used to add a new marker

##### request: multipart
* part named `message`:
	```
	authenticationToken: String
	issue: Issue
	```
* part named `image` (only exists if issue has image):
	* contains image as JPEG file
	* filename matches `message.issue.imageFilename`
	* the filename extension is `.jpg`

##### response:
```
issue: Issue
```


## `/issue/update`
* used to update an existing marker
* only allowed if marker not yet registered

##### request: multipart
* part named `message`:
	```
	authenticationToken: String
	issue: Issue
	```
* part named `image` (only exists if updated issue has image _and_ image has changed):
	* contains image as JPEG file
	* filename matches `message.issue.imageFilename`
	* the filename extension is `.jpg` 

##### response:
```
issue: Issue
```


## `/issue/delete`
used to remove an existing marker  
only allowed if marker not yet registered

##### request:
```
authenticationToken: String
issueID: UUID
```

##### response:
empty data


## `/issue/mark`
* used to mark an issue as favorite
* always allowed

##### request:
```
authenticationToken: String
issueID: UUID
```

##### response:
```
issue: Issue
```


## `/issue/review`
* used to mark an existing issue as reviewed
* only allowed if already registered, but craftsman completion not necessary

##### request:
```
authenticationToken: String
issueID: UUID
```

##### response:
```
issue: Issue
```


## `/issue/revert`
* used to revert the state of an issue
* this reopens an issue (if reviewed), or undoes the answer of the craftman
* only allowed once craftsman has marked issue as completed or if already reviewed

##### request:
```
authenticationToken: String
issueID: UUID
```

##### response:
```
issue: Issue
```



# Objects


## `Response`
* this is a variation of the JSend standard (https://labs.omniti.com/labs/jsend)
	* success: everything went well; some data was possibly returned (http response code 200)
	* fail: the request is invalid in some way (preconditions etc.) (http response code may differ from 200, but can still be 200)
	* error: some error occurred (http response code 500)
* fails may be instead returned as errors (e.g. if hard-to-implement), but errors should never be returned as fails
* the application is only allowed to parse a request if it expects the version value 
```
version: Integer
status: String // "success"/"fail"/"error"
data: Object // exists if "success" (missing otherwise)
error: Error // exists if "fail"
message: String // exists if not "success". not user-facing (but developer-readable if reasonable)
```

## `Response.Error`
* represented as `Int` for transfer purposes
```
invalidRequest = 1 // something in the request was malformed, e.g. missing value in json or missing image in multipart
invalidToken = 2 // the authentication token is invalid, e.g. because it has expired
outdatedClient = 3 // the client is outdated, thus the server won't risk communicating with it
unknownUsername = 100
wrongPassword = 101
issueAlreadyExists = 200
issueNotFound = 201
outdatedData = 202 // the server ignored the issue change attempt because the client's data is outdated
invalidAction = 203 // whatever the client is trying to do is impossible, e.g. reviewing a closed issue
```


## `SHA256`
* represented as `String` for transfer purposes
* lowercase, e.g. `f0e4c2f76c58916ec258f246851bea091d14d4247a2fc3e18694461b1816e13b`


## `UUID`
* represented as `String` for transfer purposes
* uppercase, e.g. `D366E820-6EF1-4FDE-8B78-7B8D696D4C05`


## `Date`
* represented as `String` for transfer purposes
* ISO-8601 timestamp, e.g. `2018-03-12T18:01:45.347956`


## `Color`

* represented as `String` for transfer purposes
* hex string, #RRGGBB, capitalized. e.g. `#BB0044`


## `Point`
```
x: Double
y: Double
```

## `Frame`
origin is top left
```
startX: Double
startY: Double
height: Double
width: Double
```

## `File`
```
id: UUID
filename: String
```


## `ObjectMeta`
* used for /update
```
id: UUID
lastChangeTime: Date
```


## `User`
```
meta: ObjectMeta
authenticationToken: String
givenName: String
familyName: String
```


## `ConstructionSite`
```
meta: ObjectMeta
name: String
address: Address
image: File?
maps: [UUID]
craftsmen: [UUID]
```


## `Address`
```
streetAddress: String? // first two address lines (multiline)
postalCode: Int?
locality: String?
country: String?
```


## `Craftsman`
```
meta: ObjectMeta
name: String
trade: String // e.g. "Gipser", "Maler"
```


## `Map`
```
meta: ObjectMeta
children: [UUID]
sectors: [Sector]
sectorFrame: Frame?
issues: [UUID]
file: File?
name: String
constructionSiteID: UUID
```


## `Map.Sector`
* `points` are in counterclockwise order (origin in top left)
```
name: String
color: Color
points: [Point]
```


## `Issue`
```
meta: ObjectMeta
number: Int?
isMarked: Bool
wasAddedWithClient: Bool // "abnahmemodus"
image: File?
description: String?
craftsman: UUID?
map: UUID // only really used before registration
status: Status
position: Position?
```

### `Issue.Position`
```
point: Point
zoomScale: Double
mapFileID: UUID
```

### `Issue.Status`
* status of an issue
* authors identified by strings because app doesn't have any way of cross-referencing the possible IDs with entities
```
registration: Event? // registration in issue collection
response: Event? // response from craftsman
review: Event? // review by supervisor
```

#### `Issue.Status.Event`
* details about when an issue's status was changed and by whom
```
time: Date
author: String // the name whoever caused the event chose
```
