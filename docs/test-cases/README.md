## Test Cases

### The Project Creator...

#### Creates a project (Happy Path)
- Steps: Send a HTTP request to /projects
- Request: name=Sample
- Response: Get a successful response with project details
- Side Effects: Having a project created in the database

#### Doesn't create if the name is not preset
- Steps: Send a HTTP request to /projects
- Request: name=null
- Response: Get a failed response with a validation error
- Side Effects: Having no project presence in the database

#### Doesn't create if the name is already created
- Steps: Create a project with name `sample` and then send a HTTP request to /projects
- Request: name=sample
- Response: Get a failed response with a validation error
- Side Effects: Having only one project presence in the database

