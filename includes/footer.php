<footer class="bg-[#0a0a0a] text-white pt-10 sm:pt-16 pb-6 sm:pb-8 border-t-4 border-pcwRed w-full">
        <div class="w-full px-4 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 sm:gap-12 mb-8 sm:mb-12">

                <!-- Brand Info -->
                <div class="lg:col-span-1 text-center sm:text-left">
                    <div class="text-2xl sm:text-3xl font-bold tracking-tighter flex items-center justify-center sm:justify-start mb-3 sm:mb-4">
                        <span class="text-pcwGold font-serif">Pal Chasme Wale</span>
                    </div>
                    <p class="text-pcwGold font-serif text-base sm:text-lg italic mb-2">Est. 1991</p>
                    <p class="text-gray-300 text-lg sm:text-xl font-medium mb-4 sm:mb-6">"साफ़ नज़र, बेहतर कल"</p>
                    <div class="flex justify-center sm:justify-start space-x-3 sm:space-x-4">
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-800 flex items-center justify-center text-pcwGold hover:bg-pcwRed hover:text-white transition-colors">
                            <i class="fa-brands fa-instagram text-lg sm:text-xl"></i>
                        </a>
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-800 flex items-center justify-center text-pcwGold hover:bg-pcwRed hover:text-white transition-colors">
                            <i class="fa-brands fa-facebook-f text-lg sm:text-xl"></i>
                        </a>
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-800 flex items-center justify-center text-pcwGold hover:bg-pcwRed hover:text-white transition-colors">
                            <i class="fa-brands fa-whatsapp text-lg sm:text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Locations -->
                <div class="lg:col-span-2 text-center sm:text-left">
                    <h4 class="text-pcwGold text-base sm:text-lg font-bold mb-4 sm:mb-6 uppercase tracking-wider">Our Stores</h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
                        <div>
                            <h5 class="text-white font-semibold flex items-center justify-center sm:justify-start gap-2 mb-2 sm:mb-3">
                                <i class="fa-solid fa-location-dot text-pcwRed"></i> Head Office
                            </h5>
                            <p class="text-gray-400 text-xs sm:text-sm leading-relaxed">
                                G.N. Road, Peepal Wali Gali,<br>
                                Sultanpur, Uttar Pradesh
                            </p>
                        </div>

                        <div>
                            <h5 class="text-white font-semibold flex items-center justify-center sm:justify-start gap-2 mb-2 sm:mb-3">
                                <i class="fa-solid fa-location-dot text-pcwRed"></i> Branch
                            </h5>
                            <p class="text-gray-400 text-xs sm:text-sm leading-relaxed">
                                Jail Road, Ground Floor,<br>
                                Sultanpur<br>
                                <span class="italic text-gray-500">(Nandani Eye Care ke samne)</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact & Legal -->
                <div class="lg:col-span-1 text-center sm:text-left">
                    <h4 class="text-pcwGold text-base sm:text-lg font-bold mb-4 sm:mb-6 uppercase tracking-wider">Contact Us</h4>

                    <ul class="space-y-3 sm:space-y-4 text-gray-400 text-xs sm:text-sm">
                        <li class="flex items-start justify-center sm:justify-start gap-2 sm:gap-3">
                            <i class="fa-solid fa-phone mt-1 text-pcwRed"></i>
                            <div>
                                <p>9005632555</p>
                                <p>8840404284</p>
                            </div>
                        </li>
                        <li class="flex items-center justify-center sm:justify-start gap-2 sm:gap-3">
                            <i class="fa-brands fa-instagram text-pcwRed"></i>
                            <a href="#" class="hover:text-white transition-colors">@palchasmewale_01991</a>
                        </li>
                        <li class="flex items-center justify-center sm:justify-start gap-2 sm:gap-3 pt-4 border-t border-gray-800">
                            <span class="text-pcwGold font-semibold">GSTIN:</span>
                            <span>09FIDPP0678D1Z3</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center gap-3 sm:gap-4">
                <p class="text-gray-500 text-xs sm:text-sm">&copy; 2026 Pal Chasme Wale. All rights reserved.</p>
                <div class="flex gap-4 text-xs sm:text-sm text-gray-500">
                    <a href="/privacy.php" class="hover:text-white">Privacy Policy</a>
                    <a href="/terms.php" class="hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Simple script to toggle mobile menu
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
            }
        }

        // Hero Slider Logic
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.remove('opacity-0');
                    slide.classList.add('opacity-100');
                    slide.style.zIndex = "10";
                } else {
                    slide.classList.remove('opacity-100');
                    slide.classList.add('opacity-0');
                    slide.style.zIndex = "0";
                }
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        }

        // Auto-play slider every 5 seconds
        setInterval(nextSlide, 5000);

        // Initialize first slide
        showSlide(currentSlide);
    </script>
</body>
</html>
