with open('index.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix unclosed buttons or links that were messed up by global string replace earlier
content = content.replace('Learn More &rarr;</button>', 'Learn More &rarr;</a>')
content = content.replace('Learn More &rarr;</a>', 'Learn More &rarr;</button>')
# Oh wait, the original template had `<button class="...">Learn More &rarr;</button>`
content = content.replace('<a href="/shop.php?category=tested-lenses" class="text-pcwRed text-[10px] sm:text-base font-bold hover:text-pcwBlack transition-colors tracking-wide text-left mt-auto">Learn More &rarr;</button>', '<button class="text-pcwRed text-[10px] sm:text-base font-bold hover:text-pcwBlack transition-colors tracking-wide text-left mt-auto">Learn More &rarr;</button>')
# Actually I never modified the Learn More buttons intentionally, let me just revert that whole section if it's broken or just find all `<button class="text-pcwRed...` and make sure they are matched.
