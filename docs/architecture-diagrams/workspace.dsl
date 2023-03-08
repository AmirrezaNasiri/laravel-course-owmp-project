workspace {
  model {
    guest = person "Guest"
    user = person "Verified User"
    
    emailInbox = softwareSystem "Email Provider" "User's email inbox" {
      tags "External System"
    }
    
    system = softwareSystem "Online Work Managment Platform" {
      database = container "Database" "Stores system data" {
        technology "Mysql"
        tags "Database"
      }

      fileStorage = container "File Storage" "Stores uploded files" {
        technology "S3"
      }

      redis = container "Redis" "Stores data in memory for caching and massaging purpose" {
        technology "Redis"
        tags "Database"
      }

      soketi = container "Soketi" "A simple web-socket server to send push notifications" {
        -> redis "Watches for notification messages"
        -> user "Push notification messages"
      }

      apiSystem = container "API System" "Provides business logic and interaction APIs" {
        emailService = component "Email Service" {
          -> emailInbox "Send emails"
        }

        eventBus = component "Event Bus"

        notificationManager = component "Notification Manager" {
          -> emailInbox "Sends notification emails"
          -> redis "Stores push notification messages"
        }

        authService = component "Authentication Service" {
          -> database "Store/retrieve user information"
          -> emailService "Send verification email"
          -> eventBus "If the request contains invitation identity, add them to the project (Dispatches via event)"
        }

        accountService = component "Account Service" {
          -> database "Update user information"
          -> emailService "Send verification email"
        }

        outputGeneratorService = component "Project Exporter Service" {
          -> fileStorage "Upload generated files"
        }

        group "Project Components" {
          projectIamService = component "Project IAM Service" {
            -> database "Retrieves resources"
            -> database "Modifies project roles"
            -> database "Modifies users access"
            -> projectIamService "Authorizes access to IAM modifications"
            -> notificationManager "Sends invitation email with a signed URL"
            -> eventBus "Listens to project invitation events"
          }

          projectAnalyzerService = component "Projcet Analyzer Service" "Provides analysis reports about an specific project" {
            -> database "Prepares desired report"
            -> outputGeneratorService "Generates outputs"
            -> projectIamService "Authorizes access"
            -> notificationManager "Notifies user"
          }

          projectService = component "Project Service" "Manages project entity" {
            -> database "Retrieves or modifies a project"
            -> projectIamService "Authorizes access"
          }
        }

        group "Board Components" {
          boardIamService = component "Project Board IAM Service" {
            -> database "Retrieves resources"
            -> database "Modifies project users access"
            -> boardIamService "Authorizes access to IAM modifications"
            -> projectIamService "Authorizes access to IAM modifications"
            -> notificationManager "Notifies user"
          }

          boardService = component "Board Service" {
            -> database "Retrieves or modifies a project board"
            -> boardIamService "Authorizes access"
          }          
        }

        group "Task Components" {
          taskIamService = component "Board Task IAM Service" {
            -> database "Retrieves resources"
            -> boardIamService "Authorizes access to the board and project"
          }

          taskService = component "Task Service" {
            -> database "Retrieves or modifies a board task"
            -> taskIamService "Authorizes access"
            -> notificationManager "Notifies other members in case of mention, asignment, deadline and etc."
          }  
        }
      }
    }

    # Authentication
    guest -> authService "Signs-up and signs-in"
    guest -> authService "Verifies email via a link"
    guest -> authService "Makes a password reset request"
    guest -> emailInbox "Reads the verification email"

    # Profile
    user -> accountService "Update account information"

    # Project
    user -> projectService "Retrieves or upserts projects"  
    user -> projectAnalyzerService "Requests a report analysis"
    user -> projectIamService "Invites a user to the project"
    user -> projectIamService "Manages project roles"
    user -> projectIamService "Manages users access"

    # Boards
    user -> boardService "Retrieves or upserts a board"
    user -> boardIamService "Manages users access"

    # Tasks
    user -> taskService "Retrieves or upserts a task"
    user -> taskService "Comments and mentions other members"
    user -> taskService "Modifies deadline"
    user -> taskService "Assigns other members"
    

  }
  views {
    systemlandscape "SystemLandscape" {
      include *
      autoLayout
    }
    
    container system "system-containers" {
      include *
      autolayout
    }

    component apiSystem "authentication-service" {
      include ->authService->
      autolayout
    }

    component apiSystem "account-service" {
      include ->accountService->
      autolayout
    }

    component apiSystem "project-service" {
      include ->projectService-> ->projectAnalyzerService-> ->projectIamService->
      autolayout
    }

    component apiSystem "board-service" {
      include ->boardService-> ->boardIamService->
      autolayout
    }

    component apiSystem "task-service" {
      include ->taskService-> ->taskIamService->
      autolayout
    }

    component apiSystem "database-ingress" {
      include ->database
      autolayout
    }

    dynamic apiSystem "update-project" "Update project information." {
      user -> projectService "Request a project to be updated"
      projectService -> database "Retrieve project information for future use"
      projectService -> projectIamService "Can user update this resource?"
      projectIamService -> projectService "Yes"
      projectService -> database "Update information"
      autoLayout
    }

    dynamic apiSystem "generate-report-files" "Generate a project-specific report" {
      user -> projectAnalyzerService "Request a report"
      projectAnalyzerService -> database "Retrieve basic project info"
      projectAnalyzerService -> projectIamService "Can user generate this report?"
      projectIamService -> projectAnalyzerService "Yes"
      projectAnalyzerService -> database "Prepare report data (Dispatch as a queue)"
      projectAnalyzerService -> outputGeneratorService "Order to generate output files (Dispatch as a queue)"
      outputGeneratorService -> fileStorage "Upload generated files"
      projectAnalyzerService -> notificationManager "Notify uploaded files"
      autoLayout
    }

    dynamic apiSystem "invite-user-to-project" "Invite user to project" {
      user -> projectIamService "Request a user invitation"
      projectIamService -> database "Retrieve basic user info if exists"
      projectIamService -> projectIamService "Authorize access for this action"
      projectIamService -> projectIamService "Generate a safe and temporary signed-url based-on their email and the project id"
      projectIamService -> notificationManager "Send email with prefered message"
      
      guest -> emailInbox "Read invitation email"
      guest -> authService "Signin/signup as a verified user if not already signed-up"
      authService -> eventBus "Send an event to add the user to the project (Dispatches)"
      projectIamService -> eventBus "Add the user to the project (Listens)"
      projectIamService -> database "Give access to the user"
      autoLayout
    }

    dynamic apiSystem "project-aim-add-role" "Define a new project role" {
      user -> projectIamService "Request to add a role"
      projectIamService -> database "Retrieves user and project resources"
      projectIamService -> projectIamService "Authorizes user's capability to manage the project roles"
      projectIamService -> projectIamService "Collect pre-defined system permissions"
      projectIamService -> database "Add the new role"
      autoLayout
    }

    dynamic apiSystem "add-project-member-to-a-board" "Add a project member to a board" {
      user -> boardIamService "Requests to add a user to a board"
      boardIamService -> database "Retrieves the users, project, and board resources"
      boardIamService -> projectIamService "Authorizes requester's capability to add a new board member"
      boardIamService -> database "Checks if the target user is a project member"
      boardIamService -> database "Checks if the target user is not already a board member"
      boardIamService -> database "Grants the board access to the target user"
      boardIamService -> notificationManager "Notifies the target user"
      autoLayout
    }
   
    theme default
    
    styles {
       element "External System" {
        background #999999
        color #ffffff
      }
      
      element "Database" {
        shape Cylinder
      }
    }
  }
}