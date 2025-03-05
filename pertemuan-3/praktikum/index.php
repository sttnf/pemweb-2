<?php
ob_start();
?>
<h1 class="text-2xl font-bold">Hello world</h1>
<p class="mt-2 text-gray-600">This is the home page content.</p>


<?php
$content = ob_get_clean();
require 'components/layout.php';
Layout($content);
?>
