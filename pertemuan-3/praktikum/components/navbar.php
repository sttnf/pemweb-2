<?php
function Navbar() {
    $menu = [
        'Home' => '#',
        'About' => '#',
        'Services' => '#',
        'Contact' => '# '
    ];
    ?>
    <nav class="p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="/" class="text-white text-lg font-bold">
                <?php include_once "logo.php"; ?>
            </a>
            <ul class="flex space-x-4">
                <?php foreach ($menu as $name => $url): ?>
                    <li><a href="<?php echo $url; ?>" class="text-white hover:text-gray-300"><?php echo $name; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
    <?php
}
?>