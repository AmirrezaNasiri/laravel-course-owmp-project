# Software Architecture
This directory contains diagrams needed to understand the overall structure of the software.

## About
The diagram is based-on [C4 framework](https://c4model.com/) and is coded using [Structurizr](https://structurizr.com/) language and the diagrams are rendered using [Structurizr Lite]().

## Getting Started
1. Copy `docker-compose.yaml.example` and name it `docker-compose.yaml`
1. (Optionally) Modify volume location and desired post port
1. Run `docker-compose up`
1. Navigate to [localhost:8080](http://localhost:8080)

## Project Definition
> Guest:
> - Guests can sign-up and sign-in with their email and password
> - Guests must validate their email before accessing the platform
> 
> User:
> - User can update their profile info (name, email, bio)
> - User can update their password
> - User can update their email by verifying the new one
> - User can request password reset
> 
> Project:
> - User can create any project
> - Users with sufficient access to the project can invite other users via email
> - Project status (tasks, tags, their progress, etc.) can be exported via PDF/CSV
> 
> Boards:
> - Users with sufficient access to the project can create boards
> - Users with sufficient access to the board can invite other project members
> 
> Roles:
> - Roles are project-level definitions and each project can have different roles
> - User access is based on dynamic roles which gives specific permissions
> - Users with sufficient access to the project can manage roles
> - When a member role is changed, they are notified by email
> 
> Tasks:
> - User with sufficient access to the board can create tasks in the board with name, description and tags
> - Tasks can be assigned to others 
> - Board members with sufficient access can comment on tasks and mention others
> - Updates on the tasks will lead to email and push notifications
> - Tasks can have deadlines with multiple notification
