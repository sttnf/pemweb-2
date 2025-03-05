<?php
function Sidebar()
{
    $menu = [
        'Dashboard' => '/dashboard',
        'Profile' => '/profile',
        'Settings' => '/settings',
        'Logout' => '/logout'
    ];
    ?>
    <aside class="text-white w-48 min-h-screen p-4 h-full flex flex-col gap-4">
        <h2 class="text-lg font-bold">
            Kita
        </h2>
        <ul class="space-y-2">
            <?php foreach ($menu as $name => $url): ?>
                <li><a href="<?php echo $url; ?>" class="block p-2 hover:bg-gray-500 rounded"> <?php echo $name; ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <?php
}

?>