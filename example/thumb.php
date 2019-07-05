<?php


require __DIR__ . "/../src/Cropper.php";

//$thumb = new \CoffeeCode\Cropper\Cropper("cache");
$thumb = new \CoffeeCode\Cropper\Cropper("cache");
$thumb->flush();

echo "<p><img src='{$thumb->make("images/image.jpg", 200)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.jpg", 400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.jpg", 400,400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.jpg", 1200,628)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.jpg", 200,600)}' alt='Happy Coffe' title='Happy Coffe'></p>";

echo "<p><img src='{$thumb->make("images/image.png", 200)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.png", 400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.png", 400,400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.png", 1200,628)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/image.png", 200,600)}' alt='Happy Coffe' title='Happy Coffe'></p>";

//$thumb->flush("images/image.jpg"); //All image named
//$thumb->flush("images/image.png"); //All image named
//$thumb->flush(); //All image named