<?php
// We have a problem where DB connection dies cleanly without a 500 error when mysql root password is wrong via curl.
// But we actually DO want it to block if the user isn't logged in.
// Why did curl return 200? Because the db.php has a `die()` on catch which yields a 200 string payload natively.
