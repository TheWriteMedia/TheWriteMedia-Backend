<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submission</title>
</head>
<body>
    <h2>New Contact Form Submission</h2>
    <p><strong>Name:</strong> {{ $data['name'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Subject:</strong> {{ $data['subject'] }}</p>
    <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
    <p><strong>Message:</strong> {{ $data['message'] }}</p>
</body>
</html>
