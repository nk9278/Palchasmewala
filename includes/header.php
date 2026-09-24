<?php require_once __DIR__ . "/../config/config.php"; require_once __DIR__ . "/db.php"; require_once __DIR__ . "/functions.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pal Chasme Wale - E-Commerce Prototype</title>

    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pcwRed: '#C00000',
                        pcwGold: '#D4AF37',
                        pcwBlack: '#111111',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Smooth scrolling and base styling */
        html { scroll-behavior: smooth; }
        body { -webkit-font-smoothing: antialiased; }
        .hover-scale { transition: transform 0.3s ease; }
        .hover-scale:hover { transform: scale(1.03); }

        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            display: inline-block;
            animation: marquee 20s linear infinite;
        }
        .animate-marquee:hover {
            animation-play-state: paused;
        }

        /* Premium Sunglasses Continuous Slider CSS */
        @keyframes slide-track {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .slider-container {
            overflow: hidden;
            position: relative;
            width: 100%;
            padding: 10px 0;
        }
        .slider-track {
            display: flex;
            gap: 1.5rem;
            width: max-content;
            animation: slide-track 35s linear infinite;
        }
        .slider-track:hover {
            animation-play-state: paused;
        }
        .product-card {
            width: 280px;
            flex-shrink: 0;
        }

        .category-image {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        }
        .remote-image {
            background: #f3f4f6;
        }

        /* =========================================================
           CONSISTENT PRODUCT IMAGE FRAMES
           Keeps every eyewear/product photo the same visual size
           without stretching or distorting the original image.
           ========================================================= */
        .product-image-frame {
            width: 100%;
            height: 220px;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8f8f8;
            border-radius: 14px;
            padding: 12px;
        }

        .product-image-frame img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain !important;
            object-position: center center;
            display: block;
            margin: auto;
        }

        .premium-product-image-frame {
            width: 100%;
            height: 230px;
            min-height: 230px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8f8f8;
            border-radius: 14px;
            padding: 12px;
        }

        .premium-product-image-frame img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain !important;
            object-position: center center;
            display: block;
            margin: auto;
        }

        /* Consistent rounded corners for content boxes/cards */
        .rounded-xl,
        .rounded-lg,
        .rounded-2xl {
            overflow: hidden;
        }

        /* Product cards get a slightly softer premium radius */
        .product-card,
        .group.bg-white {
            border-radius: 14px;
        }

        /* Keep the product image area identical on smaller screens (Mobile Optimization) */
        @media (max-width: 640px) {
            .product-image-frame {
                height: 110px;
                min-height: 110px;
                padding: 6px;
                border-radius: 8px;
            }
            .premium-product-image-frame {
                height: 110px;
                min-height: 110px;
                padding: 6px;
                border-radius: 8px;
            }
            .product-card {
                width: 150px; /* Make slider items smaller on mobile */
                border-radius: 8px;
            }
            .slider-track {
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Header / Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <!-- Top Bar for small announcements (Thinner) -->
        <div class="bg-pcwBlack text-white text-[10px] sm:text-xs text-center py-1.5 tracking-wider">
            FREE SHIPPING ON ALL ORDERS| CRAFTING VISION SINCE 1991
        </div>

        <!-- Full width container for Navbar -->
        <nav class="w-full px-4 sm:px-8 lg:px-12">
            <div class="flex justify-between items-center h-16 md:h-20">

                <!-- Mobile Menu Button -->
                <div class="flex items-center lg:hidden">
                    <button type="button" class="text-gray-500 hover:text-pcwRed focus:outline-none" onclick="toggleMenu()">
                        <i class="fa-solid fa-bars text-xl md:text-2xl"></i>
                    </button>
                </div>

                <!-- Logo (Smaller/Thinner) -->
                <div class="flex-shrink-0 flex items-center justify-center cursor-pointer">
                    <a href="/" class="flex flex-col items-center leading-none">
                        <!-- Updated Logo Image -->
                        <img src="/assets/Pal_logo.png" alt="Pal Chasme Wale Logo" class="h-10 md:h-12 w-auto object-contain">
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex lg:items-center lg:space-x-8">
                    <a href="/shop.php?category=eyeglasses" class="text-gray-700 hover:text-pcwRed font-medium text-sm uppercase tracking-wide transition-colors">Eyeglasses</a>
                    <a href="/shop.php?category=sunglasses" class="text-gray-700 hover:text-pcwRed font-medium text-sm uppercase tracking-wide transition-colors">Sunglasses</a>
                    <a href="/shop.php?category=computer-glasses" class="text-gray-700 hover:text-pcwRed font-medium text-sm uppercase tracking-wide transition-colors">Computer Glasses</a>
                    <a href="/shop.php?category=contact-lenses" class="text-gray-700 hover:text-pcwRed font-medium text-sm uppercase tracking-wide transition-colors">Contact Lenses</a>
                </div>

                <!-- Icons (Search, User, Cart) -->
                <div class="flex items-center space-x-5">
                    <a href="/search.php" class="text-gray-600 hover:text-pcwRed transition-colors">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </a>
                    <a href="/login.php" class="text-gray-600 hover:text-pcwRed transition-colors hidden sm:block">
                        <i class="fa-regular fa-user text-lg"></i>
                    </a>
                    <a href="/cart.php" class="text-gray-600 hover:text-pcwRed transition-colors relative">
                        <i class="fa-solid fa-cart-shopping text-lg"></i>
                        <span class="absolute -top-2 -right-2 bg-pcwGold text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">2</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-3 shadow-lg absolute w-full">
            <a href="/shop.php?category=eyeglasses" class="block text-gray-800 hover:text-pcwRed font-medium text-lg border-b pb-2">Eyeglasses</a>
            <a href="/shop.php?category=sunglasses" class="block text-gray-800 hover:text-pcwRed font-medium text-lg border-b pb-2">Sunglasses</a>
            <a href="/shop.php?category=tested-lenses" class="block text-gray-800 hover:text-pcwRed font-medium text-lg border-b pb-2">Tested Lenses</a>
            <a href="/shop.php?category=computer-glasses" class="block text-gray-800 hover:text-pcwRed font-medium text-lg border-b pb-2">Computer Glasses</a>
            <a href="/shop.php?category=contact-lenses" class="block text-gray-800 hover:text-pcwRed font-medium text-lg">Contact Lenses</a>
        </div>
    </header>