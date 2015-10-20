<?php
header('HTTP/1.1 503 Service Unavailable');
header('Retry-After: 300'); // 5 minutes in seconds
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name=robots content=noindex>

    <link rel="stylesheet" media="all" href="/css/maintenance/maintenance.css">

    <title>Site is temporarily down for maintenance</title>
</head>

<body>
    <h1>We're Sorry</h1>

    <p>The site is temporarily down for maintenance. Please try again in a few minutes.</p>
</body>
<?php
exit;
