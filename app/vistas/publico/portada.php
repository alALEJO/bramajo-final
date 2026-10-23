<?php $estilos = ['index']; ?>
 <!doctype html>
 <html lang="es">

 <head>
     <?php require __DIR__ . '/../base/cabeza.php'; ?>
 </head>

 <body>

     <header class="header">
         <div class="header__top">
             <img class="header__logo" src="/img/bramajo-logo.png" alt="bramajo logo" />
             <button class="nav-toggle" id="navToggle" aria-label="Abrir menu">
                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
             </button>
         </div>
         <nav class="header__nav" id="headerNav">
             <a class="nav__link" href="/">
                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                 Portada
             </a>
             <a class="nav__link" href="#quienes_somos">
                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                 Quienes somos
             </a>
             <a class="nav__link" href="#contenido">
                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                 Contenido
             </a>
             <a class="nav__link" href="#contacto">
                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/><path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/></svg>
                 Contacto
             </a>
         </nav>
     </header>
     <main>
         <section id="gestion" class="gestion">

             <h2 class="gestion_titulo">
                 Gestiona torneos de forma simple
             </h2>

             <p class="gestion_desc">
                 Organiza competencias deportivas, mentales y electrónicas desde una única plataforma.
             </p>
                <nav class="boton_comenzar">
                            <a class= "link_comenzar" href= "/inicio">
                                Comenzar
                            </a>
                       
             </nav>

         </section>

         <section id="quienes_somos" class="presentacion">

             <h1 class="titulo1">¿Quiénes somos?</h1>

             <p class="somos">
                 Somos <strong>BRAMAJO</strong>, un equipo de estudiantes que desarrolló
                 una plataforma pensada para facilitar la organización y participación
                 en torneos deportivos.
             </p>

             <p class="somos">
                 Nuestro objetivo es crear un espacio sencillo y fácil de usar donde
                 los usuarios puedan crear torneos, participar en ellos, consultar
                 resultados y mantenerse informados sobre los diferentes encuentros.
             </p>

         </section>

         <section class="sobre_bramajo">

             <h2 class="titulo2">¿Qué es BRAMAJO?</h2>

             <p class="somos">
                 BRAMAJO es una aplicación web creada para organizar torneos de una
                 manera más simple y ordenada. La plataforma permite gestionar
                 diferentes aspectos de un torneo desde un mismo lugar.
             </p>

             <div class="caracteristicas">

                 <div class="card">
                     <h3 class="titulo3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true" style="color:var(--color-warning);"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg> Crear torneos</h3>
                     <p class="somos">
                         Los usuarios pueden crear y organizar sus propios torneos.
                     </p>
                 </div>

                 <div class="card">
                     <h3 class="titulo3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true" style="color:var(--color-primary);"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg> Participar</h3>
                     <p class="somos">
                         Los usuarios pueden encontrar torneos y participar en ellos.
                     </p>
                 </div>

                 <div class="card">
                     <h3 class="titulo3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true" style="color:var(--color-success);"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg> Resultados</h3>
                     <p class="somos">
                         Permite consultar los resultados y el desarrollo de los
                         diferentes torneos.
                     </p>
                 </div>

             </div>

         </section>

         <section class="objetivo">

             <h2 class="titulo2">Nuestro objetivo</h2>

             <p class="somos">
                 Buscamos que BRAMAJO sea una herramienta práctica, clara y accesible,
                 que permita organizar torneos sin complicaciones y que cualquier
                 usuario pueda utilizar fácilmente.
             </p>

         </section>
         <section id="contenido">
             <section>
             
             <h2 class="gestion_titulo">
                 Informacion
                </h2>
            </section>
        
         <section class="contenido">
             <div class="descripcion_contenido">

                 <h1 class="titulo2">Gestionar torneos de forma simple</h1>

                 <p class="contenidos">
                     BRAMAJO permite organizar competencias deportivas,
                     torneos y encuentros desde una única plataforma.
                     La idea es hacer que organizar y participar sea
                     rápido, sencillo y accesible para todos.
                 </p>

             </div>

             <img class="contenido_img"
                 src="/img/imagen1.jpeg"
                 alt="Organización de un torneo">

         </section>
         

         <section class="contenido_invertido">

             <div class="descripcion_contenido">
                 <h2 class="titulo2">Se parte del público</h2>

                 <p class="contenidos">
                     Seguí cada momento de la competencia de forma
                     simple y rápida. Consultá los próximos partidos,
                     resultados, posiciones y más información del torneo
                     sin depender de mensajes o publicaciones externas.
                 </p>
             </div>

             <img
                 class="contenido_img"
                 src="/img/imagen2.jpeg"
                 alt="Resultados de un torneo">

         </section>

         
         <section class="contenido">

             <div class="descripcion_contenido">

                 <h2 class="titulo2">Para participar</h2>

                 <p class="contenidos">
                     Participá en torneos creados por los organizadores
                     y disfrutá de todas las funcionalidades que ofrece
                     BRAMAJO para seguir y formar parte de una competencia.
                 </p>

             </div>

             <img class="contenido_img"
                 src="/img/imagen3.jpeg"
                 alt="Participantes de un torneo">
         </section>

         

         <section class="contenido_invertido">

             <div class="descripcion_contenido">

                 <h2 class="titulo2">Crear tu torneo</h2>

                 <p class="contenidos">
                     Organizá y creá tu torneo de forma gratuita y fácil.
                     Podés crear tu torneo donde quieras y en cualquier
                     momento, configurando la información necesaria para
                     que los participantes puedan conocerlo.
                 </p>

             </div>
             <img class="contenido_img"
                 src="/img/imagen4.jpeg"
                 alt="Crear un torneo">
         </section>

         <section class="tipos">

             <h2 class="titulo1">Tipos de torneos</h2>

             <div class="tipos_">

                 <article class="tipo">

                     <h3 class="titulo3">Torneo web</h3>

                     <p class="contenidos">
                         Competencias organizadas completamente
                         desde la plataforma.
                     </p>

                 </article>

                 <article class="tipo">

                     <h3 class="titulo3">Torneo de mesa</h3>

                     <p class="contenidos">
                         Torneos de juegos de mesa y diferentes
                         tipos de competencias.
                     </p>

                 </article>

                 <article class="tipo">

                     <h3 class="titulo3">Deporte físico</h3>

                     <p class="contenidos">
                         Competencias deportivas como fútbol,
                         básquet, vóley y otros deportes.
                     </p>

                 </article>

                 <article class="tipo">

                     <h3 class="titulo3">Otros</h3>

                     <p class="contenidos">
                        Cualquier otra competencia que pueda
                        organizarse mediante BRAMAJO.
                    </p>

                </article>

             </div>

         </section>

     </main>

    <section id="footer">
    <?php require __DIR__ . '/../base/pie.php'; ?>
    </section>
 </body>

 </html>