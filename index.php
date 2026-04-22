<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Coral Merchant | Editorial Commerce</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-low": "#f3f3f3",
                        "on-primary-container": "#fffbff",
                        "tertiary-fixed-dim": "#abc7ff",
                        "on-tertiary-container": "#fefcff",
                        "surface-dim": "#dadada",
                        "surface-container-lowest": "#ffffff",
                        "outline-variant": "#e3bfb2",
                        "on-error-container": "#93000a",
                        "error-container": "#ffdad6",
                        "on-tertiary-fixed-variant": "#00458f",
                        "on-tertiary-fixed": "#001b3f",
                        "on-primary": "#ffffff",
                        "on-secondary-container": "#4e6874",
                        "primary-container": "#d53e0b",
                        "secondary": "#48626e",
                        "on-secondary-fixed": "#021f29",
                        "secondary-container": "#cbe7f5",
                        "surface-variant": "#e2e2e2",
                        "surface-bright": "#f9f9f9",
                        "inverse-primary": "#ffb5a0",
                        "primary-fixed-dim": "#ffb5a0",
                        "secondary-fixed": "#cbe7f5",
                        "surface-container-high": "#e8e8e8",
                        "on-surface-variant": "#5a4138",
                        "secondary-fixed-dim": "#afcbd8",
                        "tertiary": "#005ab7",
                        "primary-fixed": "#ffdbd1",
                        "background": "#f9f9f9",
                        "on-background": "#1a1c1c",
                        "primary": "#ac2d00",
                        "on-secondary": "#ffffff",
                        "surface": "#f9f9f9",
                        "inverse-surface": "#2f3131",
                        "on-primary-fixed": "#3b0900",
                        "tertiary-container": "#0072e4",
                        "surface-container": "#eeeeee",
                        "inverse-on-surface": "#f1f1f1",
                        "tertiary-fixed": "#d7e2ff",
                        "outline": "#8f7066",
                        "surface-tint": "#b02e00",
                        "on-primary-fixed-variant": "#872100",
                        "on-tertiary": "#ffffff",
                        "error": "#ba1a1a",
                        "on-surface": "#1a1c1c",
                        "on-error": "#ffffff",
                        "on-secondary-fixed-variant": "#304a55",
                        "surface-container-highest": "#e2e2e2"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .primary-aura:focus {
            box-shadow: 0 0 0 4px rgba(172, 45, 0, 0.15);
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-surface font-body text-on-surface">
<!-- TopNavBar -->
<header class="fixed top-0 w-full z-50 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md shadow-sm dark:shadow-none">
<div class="flex justify-between items-center w-full px-8 h-20 max-w-[1440px] mx-auto">
<div class="flex items-center gap-12">
<a class="text-2xl font-black text-orange-700 dark:text-orange-500 tracking-tighter" href="#">Coral Merchant</a>
<!-- Search Bar -->
<div class="hidden lg:flex items-center w-96 relative">
<input class="w-full bg-surface-container-low border-none rounded-md py-2.5 px-10 text-sm focus:ring-2 focus:ring-primary/20 primary-aura transition-all" placeholder="Search curated collections..." type="text"/>
<span class="material-symbols-outlined absolute left-3 text-zinc-400 text-lg">search</span>
</div>
</div>
<nav class="hidden md:flex items-center gap-8 font-manrope tracking-tight font-semibold">
<a class="text-orange-700 dark:text-orange-500 border-b-2 border-orange-700" href="#">Shop</a>
<a class="text-zinc-600 dark:text-zinc-400 hover:text-orange-700 hover:opacity-80 transition-opacity duration-300" href="#">Collections</a>
<a class="text-zinc-600 dark:text-zinc-400 hover:text-orange-700 hover:opacity-80 transition-opacity duration-300" href="#">New Arrivals</a>
<a class="text-zinc-600 dark:text-zinc-400 hover:text-orange-700 hover:opacity-80 transition-opacity duration-300" href="#">Sustainability</a>
</nav>
<div class="flex items-center gap-6">
<button class="cursor-pointer active:scale-95 transition-transform text-zinc-600 hover:text-orange-700">
<span class="material-symbols-outlined" data-icon="person">person</span>
</button>
<button class="cursor-pointer active:scale-95 transition-transform text-zinc-600 hover:text-orange-700 relative">
<span class="material-symbols-outlined" data-icon="shopping_cart">shopping_cart</span>
<span class="absolute -top-2 -right-2 bg-primary text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">3</span>
</button>
</div>
</div>
</header>
<main class="pt-20 max-w-[1440px] mx-auto px-8 flex gap-8">
<!-- SideNavBar -->
<aside class="hidden lg:flex flex-col sticky top-20 h-[calc(100vh-5rem)] w-64 bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 py-8">
<div class="px-6 mb-6">
<h3 class="font-manrope text-orange-700 dark:text-orange-500 font-bold uppercase tracking-widest text-xs">Categories</h3>
<p class="text-zinc-400 text-xs mt-1">Explore curated collections</p>
</div>
<nav class="space-y-1">
<a class="flex items-center px-6 py-3 space-x-4 text-orange-700 dark:text-orange-500 font-bold bg-zinc-100 dark:bg-zinc-800 transition-colors" href="#">
<span class="material-symbols-outlined text-lg" data-icon="devices">devices</span>
<span class="font-manrope text-sm">Electronics</span>
</a>
<a class="flex items-center px-6 py-3 space-x-4 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" href="#">
<span class="material-symbols-outlined text-lg" data-icon="chair">chair</span>
<span class="font-manrope text-sm">Home &amp; Office</span>
</a>
<a class="flex items-center px-6 py-3 space-x-4 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" href="#">
<span class="material-symbols-outlined text-lg" data-icon="checkroom">checkroom</span>
<span class="font-manrope text-sm">Fashion</span>
</a>
<a class="flex items-center px-6 py-3 space-x-4 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" href="#">
<span class="material-symbols-outlined text-lg" data-icon="spa">spa</span>
<span class="font-manrope text-sm">Health &amp; Beauty</span>
</a>
<a class="flex items-center px-6 py-3 space-x-4 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" href="#">
<span class="material-symbols-outlined text-lg" data-icon="laptop">laptop</span>
<span class="font-manrope text-sm">Computing</span>
</a>
</nav>
<div class="mt-auto px-6 py-8">
<div class="p-4 bg-primary-fixed rounded-xl">
<p class="text-on-primary-fixed text-xs font-bold mb-2">Member Rewards</p>
<p class="text-on-primary-fixed-variant text-[10px] leading-tight mb-4">Earn 5% back on every purchase with Coral Plus.</p>
<button class="w-full bg-primary text-white text-[10px] py-2 rounded-lg font-bold">Join Now</button>
</div>
</div>
</aside>
<!-- Main Content Canvas -->
<section class="flex-1 py-8 space-y-12">
<!-- Hero Carousel Area (Asymmetric Layout) -->
<div class="relative w-full h-[480px] bg-surface-container-low rounded-2xl overflow-hidden flex items-center">
<div class="w-1/2 p-16 space-y-6 z-10">
<span class="inline-block px-3 py-1 bg-tertiary-fixed text-on-tertiary-fixed text-[10px] font-bold tracking-widest uppercase rounded-full">New Arrival</span>
<h1 class="text-5xl font-extrabold font-headline leading-tight tracking-tighter text-on-surface">Precision Craft <br/>Modern Living</h1>
<p class="text-zinc-500 max-w-sm">Experience the next generation of home computing. Designed for the minimalist, engineered for the professional.</p>
<div class="flex gap-4 pt-4">
<button class="bg-primary hover:bg-primary-container text-white px-8 py-3 rounded-md font-bold transition-all transform active:scale-95 shadow-lg shadow-primary/20">Explore Series</button>
<button class="text-primary font-bold px-8 py-3 rounded-md border border-primary/20 hover:bg-primary/5 transition-all">Details</button>
</div>
</div>
<div class="absolute inset-0 left-1/3">
<img alt="Product Hero" class="w-full h-full object-cover" data-alt="Modern sleek laptop on a minimalist white desk with soft sunlight and a small green plant in the corner" src="https://lh3.googleusercontent.com/aida-public/AB6AXuARiZxdnhciSJJjQO_I0dAZJ-kkiepJkapPZ5uerroHiJTSjLVYRvrAuAbx1H4Sy3VGm0mYghZZmIPPJd8xnZAHtO1j5EVlODEl_qng4YceQHsO-3EVLCipNLcung1nqoIGhGsXDth_nIfepKPHqK_wkiHQROWXxjtDNrDHLRuxxSz-ftUjs0cQDD2iW1mbEJTxasGzTFz2GoUo4fBya7BSZLHnU2s6HmpoYdPAxr7-Pr9GlFVTsSLkwTH3zCOrKajff4awXF5YRDkj"/>
<div class="absolute inset-0 bg-gradient-to-r from-surface-container-low via-surface-container-low/20 to-transparent"></div>
</div>
<!-- Pagination Dots -->
<div class="absolute bottom-8 left-16 flex gap-2">
<div class="w-8 h-1.5 bg-primary rounded-full"></div>
<div class="w-1.5 h-1.5 bg-zinc-300 rounded-full"></div>
<div class="w-1.5 h-1.5 bg-zinc-300 rounded-full"></div>
</div>
</div>
<!-- Bento Grid: Featured Categories -->
<div class="grid grid-cols-4 grid-rows-2 gap-6 h-[500px]">
<div class="col-span-2 row-span-2 bg-surface-container-lowest rounded-2xl overflow-hidden group relative">
<img alt="Watch" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" data-alt="Premium minimalist white wristwatch with silver accents on a clean aesthetic background" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA0kc_BQP49xel-hHETpZn2gTSsD2BGO3cMVomI2xestqui3E54M8YUSQHkSOMKWs-rOKMb9hIW1K9bo0irx4UbqfbYHJIgDKur6wKE69XPw61yoyeC4po76OTqsfgsJBbeURR_vXDcNqFB8FxX6XV42zc6UuKsMrLX7UXIg_10ThQ9_N_QLpoSTDEQMNjjAWrn0Y4t4YtcfoMFXJK5k6iEWtr-nYhQVdn9WQL8tdlSbmCdlOQF9Fad0jZSyencWUyJ-HFlGeU6T193"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-8">
<p class="text-white/80 text-xs font-bold tracking-widest uppercase mb-1">Timepieces</p>
<h3 class="text-white text-2xl font-bold font-headline">Editorial Selection</h3>
<button class="mt-4 text-white text-sm font-semibold flex items-center gap-2 hover:translate-x-2 transition-transform">
                            Shop Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
</div>
<div class="col-span-2 bg-secondary-container rounded-2xl overflow-hidden flex items-center group">
<div class="p-8 w-1/2">
<h3 class="text-on-secondary-container text-xl font-bold font-headline">Audio Core</h3>
<p class="text-on-secondary-container/70 text-xs mt-2">Silence defined by design.</p>
</div>
<div class="w-1/2 h-full">
<img alt="Headphones" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Professional studio headphones on a textured neutral surface with cinematic lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDUAIFXBoILYbQo2HqiwocPgYyouAvDDHCOTSCLJIKriMzCrFGZsn8TCtuL-ef9fOAz8uPKxaGNjt_J3QkuKZBxpwrl2Aac_KBMaWcpyWxFEHyeYeQ-DGm9XaLeratMqOTUpQYV9fbh-vqKHeOh-paqDoJnB9Riau0XE6Ddmjdq7vef1nfxQ3pJhT3bFJsnj4rvimhKI8Jejmjs2EX2GLVcPA108j2Kj9Z-MubIX6V4RxpGOcpphJRBd2bYVvpG8tL1oP149uZp0mCt"/>
</div>
</div>
<div class="bg-surface-container-low rounded-2xl flex flex-col items-center justify-center p-6 text-center group hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-4xl text-primary mb-4 group-hover:text-white" data-icon="bolt">bolt</span>
<h4 class="font-bold text-sm group-hover:text-white">Flash Deals</h4>
<p class="text-[10px] text-zinc-500 mt-1 group-hover:text-white/80">Up to 70% Off</p>
</div>
<div class="bg-surface-container-low rounded-2xl flex flex-col items-center justify-center p-6 text-center group hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-4xl text-primary mb-4 group-hover:text-white" data-icon="star">star</span>
<h4 class="font-bold text-sm group-hover:text-white">Bestsellers</h4>
<p class="text-[10px] text-zinc-500 mt-1 group-hover:text-white/80">Community Choice</p>
</div>
</div>
<!-- Flash Sales / Today's Deals -->
<section class="space-y-6">
<div class="flex justify-between items-end">
<div class="flex items-center gap-6">
<h2 class="text-3xl font-black font-headline tracking-tighter">Today's Deals</h2>
<div class="flex items-center gap-2 bg-orange-100 text-orange-700 px-3 py-1 rounded-md">
<span class="text-xs font-bold">Ends in</span>
<span class="font-mono font-bold text-sm">04:21:55</span>
</div>
</div>
<a class="text-primary font-bold text-sm border-b border-primary/20 pb-1" href="#">View all deals</a>
</div>
<div class="grid grid-cols-4 gap-8">
<!-- Product Card 1 -->
<div class="space-y-4 group">
<div class="aspect-[4/5] bg-surface-container-lowest rounded-xl overflow-hidden relative">
<img alt="Smartwatch" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Minimalist smartwatch with a grey fabric band against a clean concrete background" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLQhk8SSB3Vkeia15atyeSKb7f88nbp0T8mUFHGnt9mFNU01ivlYrN11dipnf7RBNalnHCPjEzgQ_mdSEsGfuoJEgf0GMN89hu3MNx1EUfRFGVkgFXMe0-T4gDJfPdZCEf_RZKRVXI6uGsx87dPvzlxtlsqsvimoe7rcsDDtXEiC-ckFa2YNZs3ecxgv8ZTAwM3W3AfCeyfhdyDQccMouYSxutTXsSQYByZS0l2sXQ_VehLRJEXVr_CCHQ68JF_m8WPxTCQljrTH7S"/>
<span class="absolute top-4 left-4 bg-primary text-white text-[10px] px-2 py-1 font-bold rounded">-45%</span>
<button class="absolute bottom-4 right-4 bg-white/90 backdrop-blur text-on-surface p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg active:scale-90">
<span class="material-symbols-outlined text-lg">add_shopping_cart</span>
</button>
</div>
<div>
<p class="text-zinc-400 text-[10px] font-bold tracking-widest uppercase">Electronics</p>
<h4 class="font-bold text-sm mt-1">Core Series S-21 Smartwatch</h4>
<div class="flex items-center gap-2 mt-2">
<span class="text-primary font-black">$129.00</span>
<span class="text-zinc-400 line-through text-xs">$240.00</span>
</div>
</div>
</div>
<!-- Product Card 2 -->
<div class="space-y-4 group">
<div class="aspect-[4/5] bg-surface-container-lowest rounded-xl overflow-hidden relative">
<img alt="Sneakers" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Classic white leather sneakers on a minimal aesthetic background with soft shadows" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3zIIbEJdRnMCdG_zyui9odVJzzK4CcCRXt-LzSRqCxFGBAN4KoM0ID8HqVewzRYQcvnWlIKQNva7J5vCYa4DrjEvmjV76ZG1U3Fzjo7ycGRpelJAIClQ_m1f6Oim9Bbj_u5H0wxriP6ZXu4MRQ4Q6UQ68Midmja6-bI4Ga699P1dYAOPadS3bP8akI4ViiJaZ6nwLqF_xTNJ_gbULWeNMVzRQrxAzXIZr82CDLNc1ZbUtGG6vsxKppSb0dnS1tP4P5rHqYmSWqODr"/>
<span class="absolute top-4 left-4 bg-primary text-white text-[10px] px-2 py-1 font-bold rounded">-20%</span>
<button class="absolute bottom-4 right-4 bg-white/90 backdrop-blur text-on-surface p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg active:scale-90">
<span class="material-symbols-outlined text-lg">add_shopping_cart</span>
</button>
</div>
<div>
<p class="text-zinc-400 text-[10px] font-bold tracking-widest uppercase">Fashion</p>
<h4 class="font-bold text-sm mt-1">Heritage Low-Top Sneaker</h4>
<div class="flex items-center gap-2 mt-2">
<span class="text-primary font-black">$85.00</span>
<span class="text-zinc-400 line-through text-xs">$110.00</span>
</div>
</div>
</div>
<!-- Product Card 3 -->
<div class="space-y-4 group">
<div class="aspect-[4/5] bg-surface-container-lowest rounded-xl overflow-hidden relative">
<img alt="Audio" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Professional dark studio microphone with mesh filter and metallic details" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB-7JultXtZFLOHjm3vIqMAInobAw-qZVp1cMGeoRZTpQApn61X8W4EWG29nwDW-xSFgP5iiOHU9zwEciAgPBF_ZcKxHJlLBtxdYJSHxDmwYAZnA7ESeJVEJYsNl4wODVTez2LCdpiunPF1q5_XAy-fkWZRFdEbbbojpxokKMLr_Fyj5WgR7uQE8sw1LthYoekCwUxmFRaASIjdT8D6jXFdJz5QWbiEJ1B20P0utk51Rahmbg98mJ21Y3eaq1kK5fFFzN37wQbxxI2y"/>
<span class="absolute top-4 left-4 bg-primary text-white text-[10px] px-2 py-1 font-bold rounded">New</span>
<button class="absolute bottom-4 right-4 bg-white/90 backdrop-blur text-on-surface p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg active:scale-90">
<span class="material-symbols-outlined text-lg">add_shopping_cart</span>
</button>
</div>
<div>
<p class="text-zinc-400 text-[10px] font-bold tracking-widest uppercase">Computing</p>
<h4 class="font-bold text-sm mt-1">Vox-Pro Studio Microphone</h4>
<div class="flex items-center gap-2 mt-2">
<span class="text-primary font-black">$299.00</span>
<span class="text-zinc-400 line-through text-xs">$350.00</span>
</div>
</div>
</div>
<!-- Product Card 4 -->
<div class="space-y-4 group">
<div class="aspect-[4/5] bg-surface-container-lowest rounded-xl overflow-hidden relative">
<img alt="Camera" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Vintage-style modern digital camera with leather strap on a wooden tabletop" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD0XLnYMiL3BWf-yfFu_Y7oR2j9GVyfeFC1v-ppVWqV7-lxKzn1sRXfXOVy355k6IW7gsAZL8X1wkGExEBOC2oSzCS7uQMcPsc5bTOnCisZSJio1OBzG1O9LMu45vGsN0u-HCFh7D43B87lViJH420tD6yT6SSrHPsS8atOFJqHbZbWcQiqegqVxUm0BCdFfroqbqceprfFSNum7dJj-XTtcsnPJB62WIHK3DlC6NUg0drFcRoA2Z4eutADwIKZFi5rJQFEaP--ySON"/>
<span class="absolute top-4 left-4 bg-primary text-white text-[10px] px-2 py-1 font-bold rounded">-15%</span>
<button class="absolute bottom-4 right-4 bg-white/90 backdrop-blur text-on-surface p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg active:scale-90">
<span class="material-symbols-outlined text-lg">add_shopping_cart</span>
</button>
</div>
<div>
<p class="text-zinc-400 text-[10px] font-bold tracking-widest uppercase">Electronics</p>
<h4 class="font-bold text-sm mt-1">Optic X Digital Camera</h4>
<div class="flex items-center gap-2 mt-2">
<span class="text-primary font-black">$549.00</span>
<span class="text-zinc-400 line-through text-xs">$650.00</span>
</div>
</div>
</div>
</div>
</section>
<!-- Curated Collection Promotional Banner -->
<div class="bg-primary-container rounded-2xl p-12 flex items-center gap-12 overflow-hidden relative">
<div class="flex-1 space-y-6 z-10">
<h2 class="text-4xl font-black font-headline text-white leading-tight">The Sustainable <br/>Home Office</h2>
<p class="text-white/80 max-w-sm">Discover products made with the future in mind. Eco-friendly materials without compromising on high-end performance.</p>
<button class="bg-white text-primary px-8 py-3 rounded-md font-bold hover:bg-zinc-100 transition-colors">Shop Collection</button>
</div>
<div class="w-1/3 h-64 relative -mb-24">
<img alt="Eco-friendly office" class="w-full h-full object-cover rounded-xl shadow-2xl" data-alt="Minimalist wooden desk setup with natural light and green plants for a sustainable home office aesthetic" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBzQj7-9EcNjZ9H9wvo1iyjZHmAx5oAesnmwl6tpot2GdTvkWjyScoBXIE4-IC8Qj-JDwoZN_1nnA1H0oydMIKwmCBTGgn1mn0dhB9SZZYGzU-qdfVdyfvDg69a0A55IdkVrMarr4Rfz722OA6EmsoC0tfEbgubLjaVDb5hnzWIE6nV5lxks6tfXaEDqJIGQP_fSC9ghAiuTn68YlFTvY0tSVfWqU4DZo9-vbb8lGGl0a_jDLZZOXjJWoieWAJtD_wEO0KWrqfKez9u"/>
</div>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-zinc-100 dark:bg-zinc-950 mt-20">
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-[1440px] mx-auto w-full py-12 px-8 border-t border-zinc-200 dark:border-zinc-800">
<div class="space-y-4">
<h4 class="text-lg font-bold text-orange-700">Editorial Commerce</h4>
<p class="font-inter text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Redefining the digital marketplace through curated intentionality and high-end design.</p>
</div>
<div class="space-y-4">
<h4 class="text-zinc-900 dark:text-zinc-100 font-bold text-sm">Customer Care</h4>
<ul class="space-y-2">
<li><a class="font-inter text-sm text-zinc-500 hover:text-orange-600 transition-all duration-200 underline decoration-orange-700/30 underline-offset-4" href="#">Help Center</a></li>
<li><a class="font-inter text-sm text-zinc-500 hover:text-orange-600 transition-all duration-200 underline decoration-orange-700/30 underline-offset-4" href="#">Contact Us</a></li>
<li><a class="font-inter text-sm text-zinc-500 hover:text-orange-600 transition-all duration-200 underline decoration-orange-700/30 underline-offset-4" href="#">Return Policy</a></li>
</ul>
</div>
<div class="space-y-4">
<h4 class="text-zinc-900 dark:text-zinc-100 font-bold text-sm">Our Company</h4>
<ul class="space-y-2">
<li><a class="font-inter text-sm text-zinc-500 hover:text-orange-600 transition-all duration-200 underline decoration-orange-700/30 underline-offset-4" href="#">About Us</a></li>
<li><a class="font-inter text-sm text-zinc-500 hover:text-orange-600 transition-all duration-200 underline decoration-orange-700/30 underline-offset-4" href="#">Terms of Service</a></li>
</ul>
</div>
<div class="space-y-4">
<h4 class="text-zinc-900 dark:text-zinc-100 font-bold text-sm">Newsletter</h4>
<p class="font-inter text-xs text-zinc-600 dark:text-zinc-400">Join our mailing list for curated weekly drops.</p>
<div class="flex gap-2">
<input class="flex-1 bg-white border-zinc-200 rounded-md text-xs py-2 focus:ring-primary" placeholder="Email address" type="email"/>
<button class="bg-primary text-white px-4 py-2 rounded-md text-xs font-bold">Join</button>
</div>
</div>
</div>
<div class="max-w-[1440px] mx-auto px-8 py-6 border-t border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
<p class="font-inter text-[10px] text-zinc-500 uppercase tracking-widest">© 2024 Editorial Commerce. All rights reserved.</p>
<div class="flex gap-4">
<span class="material-symbols-outlined text-zinc-400 text-lg">payments</span>
<span class="material-symbols-outlined text-zinc-400 text-lg">credit_card</span>
</div>
</div>
</footer>
</body></html>