<?php


require __DIR__ . "/../src/Cropper.php";

//$thumb = new \CoffeeCode\Cropper\Cropper("cache");
$thumb = new \CoffeeCode\Cropper\Cropper("cache");
$thumb->flush();

echo "<p><img src='{$thumb->make("images/guitar.jpg", 200)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/guitar.jpg", 400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/guitar.jpg", 400,400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/guitar.jpg", 1200,628)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/guitar.jpg", 200,600)}' alt='Happy Coffe' title='Happy Coffe'></p>";

echo "<p><img src='{$thumb->make("images/rock.png", 200)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/rock.png", 400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/rock.png", 400,400)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/rock.png", 1200,628)}' alt='Happy Coffe' title='Happy Coffe'></p>";
echo "<p><img src='{$thumb->make("images/rock.png", 200,600)}' alt='Happy Coffe' title='Happy Coffe'></p>";

//$thumb->flush("images/guitar.jpg"); //All image named
//$thumb->flush("images/rock.png"); //All image named
//$thumb->flush(); //All image named