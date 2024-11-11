<!DOCTYPE html>
<html>
<head>
    <title>Note-Taking Application</title>
    <!-- Bootstrap/Styling Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- General JS script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/function.js') }}"></script>
</head>
<body>     
<div class="container">
     @yield('content')
</div>    
</body>
</html>