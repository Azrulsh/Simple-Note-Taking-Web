// function to hide create,edit and delete message after 3 seconds
setTimeout(function() 
{
    var flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        flashMessage.style.display = 'none';
    }
},  3000); // the duration for message to dissapear in milisecond

// SweetAlert2 create confirmation message
$(document).on('click', '#create', function(e) 
{
    e.preventDefault(); // Prevent default action
    var form = $(this).closest('form'); // Get the closest form element
    Swal.fire({
        title: "Are you sure to create new note?",
        text:  "Your newly created note would be added!",
        icon:  "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor:  "#3085d6",
        confirmButtonText:  "Yes, create it!"
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit(); // Submit the form if confirmed
            Swal.fire({
            title: "Created!",
            text:  "Your note has been created.",
            icon:  "success"
            });
        }
    });
});

// SweetAlert2 edit confirmation message
$(document).on('click', '#edit', function(e) {
    e.preventDefault(); // Prevent default form submission
    var form = $(this).closest('form'); // Get the closest form element
    Swal.fire({
        title: "Are you sure you want to edit this note?",
        text:  "Your previous note will be overwritten!",
        icon:  "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor:  "#3085d6",
        confirmButtonText:  "Yes, edit it!"
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit(); // Submit the form if confirmed
            Swal.fire({
            title: "Edited!",
            text:  "Your note has been edited.",
            icon:  "success"
            });
        }
    });
});

// SweetAlert2 delete confirmation message
$(document).on('click', '#delete', function(e) {
    e.preventDefault(); // Prevent default action
    var form = $(this).closest('form'); // Get the closest form element
    Swal.fire({
        title: "Are you sure to delete this note?",
        text:  "This will delete your note permanently!",
        icon:  "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor:  "#3085d6",
        confirmButtonText:  "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit(); // Submit the form if confirmed
            Swal.fire({
            title: "Deleted!",
            text:  "Your note has been deleted.",
            icon:  "success"
            });
        }
    });
});




