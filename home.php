<?php 
date_default_timezone_set('Asia/Manila');
echo date('H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSE Example</title>
</head>
    <body>

    <script>
        const eventSource = new EventSource('run-sse.php');

        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            // alert(data);

            fetch('index.php')
                .then(response => response.json())
                .then(data => console.log(data))
                .catch(error => console.error('Error:', error));
        };

        eventSource.onerror = function(error) {
            console.error('EventSource failed:', error);
            eventSource.close();
        };
    </script>

    </body>
</html>