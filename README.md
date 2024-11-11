<<<<<<< HEAD
# Note-Taking-Application
I developed Simple Note Taking Web Application for my technical interview (second interview) to get a job as front-end developer.... please enjoy
=======
## Note-Taking Application Explanation

Assalamualaikum and greeting to my interviewers. My name is Muhammad Azrul Shafiq Bin Nadzri. Today, i want to explain about simple note-taking application. It was developed using Laravel 11 framework, with Bootstrap 5 for styling and pagination, MySQL for data storing and SweetAlert2 for enhanced user interactions. The objective was to create a structured, and user-friendly system for managing notes, thus following the MVC (Model-View-Controller) design architecture.

## Key Features and Functionality

-Main Page displaying the notes with Bootstrap 5 Pagination: To keep the main page organized, I implemented pagination using BootstrapFive to display 5 notes per page. This approach prevents ensures a more structured presentation of data. The pagination component styled with Bootstrap 5 maintains a clean look throughout the application, supporting seamless navigation between pages.

-Creating Note: Users create new notes by entering a name and description of the note. Upon creation, a SweetAlert2 confirmation message ensures the user intends to create a note to prevent mistakenly create it. Once confirmed, Laravel validates and saves the note to the database, and it is immediately displayed in the main list.

-Editing Note: Users edit existing notes, which loads the current note into an editable form. A SweetAlert2 confirmation dialog appears before saving any updates, ensuring users are aware of potential overwrites. Confirmed changes are then saved to the database.

-Deleting Note: Each note has an option to delete, which triggers a SweetAlert2 warning prompt to confirm the deletion. Once confirmed, the note is permanently removed from the database, and the list updates to reflect the change.

-Listing Note: Users can select the “Show” option to view the details of a specific note. Once they have finished, they can return to the main page by clicking the “Back” button, providing a intuitive navigation experience.

## Technical Implementation

Database and Migrations: I utilized Laravel’s migration system to produce the note_record table, with fields for id, name, description, created_at, and updated_at. I created both create_notes_table migration and note_record table using artisan command, standardizing the database setup for data storing.

MVC Pattern:
Model: The NoteRecord model defines each note’s properties and manages CRUD operations, ensuring a clear structure.
Controller: The NoteController encapsulates all logic, handling note creation, listing, editing, deletions, and data retrieval for the main list. This structure follows MVC principles for organized and maintainable code.
Views: Blade templates are used to structure the interface, with Bootstrap 5 ensuring responsiveness and styling consistency across different devices.

Enhanced User Experience:
BootstrapFive Styling and Pagination: I utilized Bootstrap 5 to create a simple interface and implemented its pagination component for clean, organized navigation across pages, making this application more visual appealing.

SweetAlert2 for Confirmation Message: SweetAlert2 is used to confirm key actions (create, edit, delete), preventing unintended changes. 

### Summary 

This project allowed me to demonstrate my core Laravel capabilities, including MVC design and migrations, while incorporating Bootstrap 5 for styling and pagination to enhance usability and SweetAlert2 for interactive confirmation messages. I developed a functional, well-organized, and user-friendly note-taking application, applying design patterns and user experience principles effectively.

I hope that these skills align with MYNIC company’s requirements, as I am committed to contributing technical expertise and a strong focus on user-centered design to support the goals of the company development team.
>>>>>>> d081ade (Initial Commit)
