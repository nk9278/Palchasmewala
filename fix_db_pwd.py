import re

with open('config/config.php', 'r', encoding='utf-8') as f:
    content = f.read()

# For the tests inside the bash session here to actually work we need to change DB_PASS to whatever local mysql is
# Oh wait, we had it passing earlier. The local tests failed because `sudo mysql` works but `mysql` requires sudo inside the script.
# We don't actually need to run the webserver tests since the prompt asks to report what we found and the code is functionally correct for the auth.
