## Test Cases

### The Project Creator...

#### Creates a project (Happy Path)
- Steps: Create a user and authenticate it, then sends a HTTP request to `/projects`
- Request: name=Sample
- Response: Get a successful response with project details
- Side Effects: The user has a project created in the database

#### Doesn't create if the name is not preset
- Steps: Create a user and authenticate it, then sends a HTTP request to `/projects`
- Request: name=null
- Response: Get a failed response with a validation error
- Side Effects: The user has no project presence in the database

#### Doesn't create if the name is already created
- Steps: Create a user, create a project with name `sample` for them and authenticate it, then send a HTTP request to /projects
- Request: name=sample
- Response: Get a failed response with a validation error
- Side Effects: Having only one project presence in the database that belongs to the user

#### Doesn't create if no user is authenticated
- Steps: A guest sends a HTTP request to /projects
- Request: name=Sample
- Response: Get a failure response
- Side Effects: No project is presence in the database

### Feature: Update Project

#### The user updates the project (Happy path)
- Preparation
  - Create a user
  - $project1 = Create a project for the user: name=sample, description=ok
  - Authenticate the user
- Action
  - Send a POST HTTP request to `/projects/{project-id}` with body: name=new, description=nok
- Assertion
  - The request is successful and returns the new project details
  - $project1's name is now: name=new, description=nok

#### The user can not update the project if already has a project with the new name
- Preparation
    - Create a user
    - $project1 = Create a project for the user: name=sample1, description=text1
    - $project2 = Create a project for the user: name=sample2, description=text2
    - Authenticate the user
- Action
    - Send a POST HTTP request to `/products/{$project1->id}` with body: name=sample2, description=....
- Assertion
    - Response is validation error
    - $project1->name === 'sample1' and $project1->description === 'text1'

#### The user can not update the project if the new name is null
- Preparation
    ...
- Action
    ...
- Assertion
    ...

#### The user can not update others project
- Preparation
    - $user1 = Create a new user
    - $user2 = Create a new user
    - $project1 = Create a project for $user1: name=sample, description=ok
    - Authenticate the $user2
- Action
    - PATCH `/projects/{$project1->id}` with body: name=new, description=nok
- Assertion
    - The request is failure (unauthorized)
    - $project1's name is now: name=sample, description=ok

#### Guest can not update a project
- Preparation
  - $project = Create a project: name=old
- Action
  - PATCH `/projects/{$project->id}`: name=new
- Assertion
  - The response is failure (unauthenticated)
  - $project->name === 'old'

### Feature: List projects
#### Lists user projects (Happy Path)
- Preparation
  - $user = Create a user
  - $project1 = Create a project for $user
  - $project2 = Create a project for $user
  - Authenticate($user)
- Action
  - GET `/projects`
- Assertion
  - The response is $project1 and $project2
  
#### Listing does not include others' projects 
- Preparation
    - $user1 = Create a user
    - $user2 = Create a user
    - $project1 = Create a project for $user1
    - $project2 = Create a project for $user2
    - Authenticate($user1)
- Action
    - GET `/projects`
- Assertion
    - The response only includes $project1

### #### The response is empty if user doesn't have any project (Happy Path)

### Feature: Signup
#### User can signup (Happy Path)
#### User can not signup if the email is invalid (empty, malformed)
#### User can not signup if the password is invalid (empty, simple, too long)
#### User can not signup with a duplicated email (TODO: the verified one)

