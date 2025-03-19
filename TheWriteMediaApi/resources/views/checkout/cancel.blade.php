<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <style>
        /* Reset default margin and padding */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        }

        /* Center the modal */
        .modal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        /* Style the heading */
        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #FF0000; /* Red color */
        }

        /* Style the paragraphs */
        p {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #333;
        }

        /* Style the "Return to Home" button */
        .home-button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: white;
            background-color: #FF0000; /* Red color */
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .home-button:hover {
            background-color: #cc0000; /* Darker red on hover */
        }
    </style>
</head>
<body>
    <div class="modal">
        <h1>Payment Cancelled</h1>
        <p>Your payment was not completed. You can try again.</p>
        <a href="https://thewritemedia-backend.onrender.com" class="home-button">Return to Home</a>
    </div>
</body>
</html>