<?php

session_start(); //Starter sessionen

session_destroy(); //slutter og sletter session med det samme

header("Location: index.php"); //omdiregerer til index.php
exit;