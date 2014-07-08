Warung-Kutu-Kutu
================

Simple e-commerce post types


Open up “category.php”, and within the Loop, where you want the content to show (normally, where the_content(); is) pop in:

<?php product_display(‘summary’); ?>


For single product, open up “single.php”, and within the Loop, where you want the content to show (normally, where the_content(); is) pop in:

<?php product_display(‘full’); ?>
