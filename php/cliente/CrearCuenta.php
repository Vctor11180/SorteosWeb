<?php
/**
 * CrearCuenta
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Registro de Usuario - Plataforma de Sorteos</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2563eb","background-light": "#f6f6f8",
                        "background-dark": "#111621",
                        "surface-dark": "#1a202c",
                        "input-dark": "#282d39",
                        "error": "#EF4444",},
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased min-h-screen flex flex-col">
<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
<div class="absolute inset-0 pointer-events-none overflow-hidden">
<div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-primary/10 blur-3xl"></div>
<div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] rounded-full bg-primary/5 blur-3xl"></div>
</div>
<div class="layout-container flex h-full grow flex-col z-10">
<header class="flex items-center justify-center py-6 px-4 sm:px-10">
<div class="flex items-center gap-3 text-white">
<div class="flex items-center justify-center size-10 rounded-lg bg-primary/20 text-primary">
<span class="material-symbols-outlined text-3xl">confirmation_number</span>
</div>
<h2 class="text-white text-xl font-bold leading-tight tracking-tight">Plataforma de Sorteos</h2>
</div>
</header>
<main class="flex flex-1 justify-center items-center px-4 py-5">
<div class="layout-content-container flex flex-col max-w-[520px] w-full flex-1">
<div class="flex flex-col gap-6 sm:p-8 sm:bg-[#161b26] sm:rounded-xl sm:border sm:border-[#282d39]">
<div class="flex flex-col gap-2 text-center sm:text-left">
<h1 class="text-white text-3xl font-black leading-tight tracking-[-0.033em]">Crear cuenta</h1>
<p class="text-[#9da6b9] text-base font-normal leading-normal">Ãšnete a nosotros para participar en los mejores sorteos.</p>
</div>
<form class="flex flex-col gap-4 mt-2" onsubmit="event.preventDefault();">
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Nombre completo</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">person</span>
</div>
<input class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="Juan PÃ©rez" type="text"/>
</div>
</label>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Correo ElectrÃ³nico</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">mail</span>
</div>
<input class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="juan@ejemplo.com" type="email"/>
</div>
</label>
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">TelÃ©fono</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">smartphone</span>
</div>
<input class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="+52 123 456 7890" type="tel"/>
</div>
</label>
</div>
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">ContraseÃ±a</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">lock</span>
</div>
<input class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" type="password"/>
</div>
</label>
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Confirmar ContraseÃ±a</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">lock_reset</span>
</div>
<input class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" type="password"/>
</div>
</label>
<label class="flex items-start gap-3 cursor-pointer mt-1">
<div class="relative flex items-center">
<input class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-[#3b4354] bg-[#282d39] checked:border-primary checked:bg-primary focus:ring-0 focus:ring-offset-0 transition-all" type="checkbox"/>
<span class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100">
<svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill-rule="evenodd"></path>
</svg>
</span>
</div>
<span class="text-sm text-[#9da6b9] leading-tight">
                                    He leÃ­do y acepto los <a class="text-primary hover:text-blue-400 hover:underline" href="#">TÃ©rminos y Condiciones</a> y la <a class="text-primary hover:text-blue-400 hover:underline" href="#">PolÃ­tica de Privacidad</a>.
                                </span>
</label>
<button class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-4 bg-primary hover:bg-blue-600 transition-colors text-white text-base font-bold leading-normal tracking-[0.015em] shadow-lg shadow-blue-900/20 mt-4">
<span class="truncate">Registrarse</span>
</button>
<div class="text-center pt-2">
<p class="text-[#9da6b9] text-sm">
                                    Â¿Ya tienes una cuenta? 
                                    <a class="font-bold text-primary hover:text-blue-400 transition-colors ml-1" href="#">Iniciar sesiÃ³n</a>
</p>
</div>
</form>
</div>
<div class="mt-10 flex justify-center gap-6 text-[#9da6b9] text-xs">
<a class="hover:text-white transition-colors" href="#">TÃ©rminos y Condiciones</a>
<a class="hover:text-white transition-colors" href="#">PolÃ­tica de Privacidad</a>
<a class="hover:text-white transition-colors" href="#">Ayuda</a>
</div>
</div>
</main>
</div>
</div>

</body></html>

//vista para crear cuenta

