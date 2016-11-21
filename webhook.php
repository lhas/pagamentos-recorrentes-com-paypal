<?php
$file_handle = fopen('post.log', 'a+');
fwrite($file_handle, print_r($_POST,true));
fwrite($file_handle, file_get_contents('php://input'));
fclose($file_handle);