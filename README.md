## Overview of Additional Task

Assalamualaikum and greeting to my interviewers. My name is Muhammad Azrul Shafiq Bin Nadzri. Today, i want to explain about additional task. This project implements a Laravel 11 framework with added modules and features designed as part of a technical interview. It includes functionality for managing Login, Notes and Remarks with proper relationships, authentication, authorization, and dynamic interaction.

## Remark Module Implementation

The Remark module facilitates the creation and management of remarks linked to Notes and Login module that have already defined on the respective model by using hasMany and belongsTo. It establishes the following relationships:

-A Note can have many Remarks.
-Each Remark belongs to a Note.
-Each Remark is created by a User.
-Multiple Users can add Remarks to the same Note.
This ensures accurate data tracking, associating each remark with its corresponding note, while preventing unauthorized access to remarks by users who are not the creators.

Key Features:
-AJAX request is used to display remark data for a Note without needing to page reload.
-Bootstrap 5 modals are used for managing remarks (adding, editing, deleting, blocking, and unblocking) to provide a responsive and interactive user interface.
-AJAX is used to perform CRUD operations on remarks without reloading the page. When a user adds, updates, or deletes a remark, an AJAX request is sent to the server. The server then sends back the updated data, which is used to refresh the content on remark page.

## Login Module Implementation

The Login module provides authentication and ensures only authorized users can perform specific actions by using Auth method.

Key Features:
-User must register and login first
-Note Module:Only the creator of a Note is permitted to edit or delete it.
-Remark Module:Only the creator of a Remark is permitted to edit or delete it.

## Note and User Relationships

To track creators, the following relationships are implemented:

-A Note belongs to a User.
-A User can have many Notes.
-This ensures proper data tracking and prevents unauthorized access to Notes by non-creators.

## Demonstrate Polymorphic relationship

To demonstrate the polymorphic relationships between the models in the application, the following features have been implemented in the user interface:

-Displaying Note Creator: On the IndexNote page, the creator of each note is displayed, showcasing the relationship between the User model and the NoteRecord model.
-Displaying Remark Creator: On the IndexRemark page, the creator of each remark is shown, illustrating the relationship between the User model and the RemarkRecord model.
-Displaying Note Name for Remarks: On the IndexRemark page, the name of the associated note is displayed, and only the remarks specific to a particular note are listed. This    
 demonstrates the relationship between the NoteRecord model and the RemarkRecord model, ensuring that remarks are properly linked to their respective notes.

## Showcasing JavaScript or jQuery Skills

I utilized JavaScript and jQuery to implement CRUD operations for the Remark module. Specifically, AJAX requests were employed to dynamically perform Create, Read, Update, and Delete operations on remarks without requiring a page reload. This approach allows for real-time updates of remark data on the interface.

