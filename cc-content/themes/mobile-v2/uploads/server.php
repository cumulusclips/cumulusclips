<?php

session_start();

exit(json_encode((object) $_SESSION));