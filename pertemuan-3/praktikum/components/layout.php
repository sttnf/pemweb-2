<?php
require_once __DIR__ . '/../components/Navbar.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../components/Footer.php';

function Layout($content)
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Website</title>
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script
    </head>
    <body class="bg-linear-45 via-purple-500 from-pink-500 to-indigo-500">
    <?php Navbar(); ?>
    <div class="flex">
        <?php Sidebar(); ?>
        <main class="flex-1 p-4 min-h-screen overflow-y-auto -mt-2 bg-white rounded-tl-xl">
            <?php echo $content; ?>
        </main>
    </div>
    <?php Footer(); ?>
    </body>
    </html>
    <?php
}

?>
