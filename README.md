# Bramajo

Sistema de Gestión Deportiva Modular de **BoomeRam Solutions**.
Proyecto de egreso 2026 · 3.º BT Tecnologías de la Información · ITS Arias Balparda.

Plataforma web para organizar torneos con tres formatos: liga, eliminación directa y sistema suizo.
PHP 8.3 orientado a objetos con MVC, MySQL 8.0 y Docker.

## Cómo está organizado

```
bramajo/
├── app/
│   ├── nucleo/         Conexion, Sesion, Validador, Controlador, Cifrado, Firma
│   ├── modelos/        una clase por tabla: Usuario, Torneo, Participante, Equipo, Ronda...
│   ├── competencia/    ModuloCompetencia (abstracta), Liga, EliminacionDirecta, SistemaSuizo
│   ├── controladores/  reciben el pedido, validan permisos y llaman a los modelos
│   └── vistas/         HTML de cada página
├── public/             index.php (punto de entrada), css, js, img
├── base_de_datos/      01 estructura · 02 usuarios y permisos (DCL) · 03 datos de prueba · Dockerfile de la base
├── docs/               DER en draw.io (DER, atributos y pasaje a tablas) y sus imágenes
├── docker/             configuración de Apache, PHP y arranque del contenedor
├── servidor/           scripts del servidor: respaldos, monitoreo, firewall, antivirus, usuarios
├── scripts/            generar_datos.php (vuelve a generar los datos de prueba)
├── tests/              pruebas unitarias (PHPUnit)
├── Dockerfile          imagen de la aplicación
└── docker-compose.yml
```

## Usuarios de prueba

Todos tienen la contraseña `Bramajo2026`.

| Rol | Correo |
|---|---|
| Administrador general | admin.prueba@bramajo.test |
| Organizador | orga.prueba@bramajo.test |
| Participante | part.prueba@bramajo.test |

Hay además 5 organizadores, 60 participantes, 50 equipos y 50 torneos en distintos estados.

## Cómo se trabaja con Docker Hub

El sistema son dos imágenes que se publican en Docker Hub:

| Imagen | Qué tiene |
|---|---|
| `USUARIO/bramajo-web` | Apache + PHP 8.3 con el código de la aplicación |
| `USUARIO/bramajo-db` | MySQL 8.0 con los scripts que crean las tablas, los usuarios y los datos de prueba |

La idea es simple: **en casa se construyen y se suben; en el servidor solo se descargan y se ejecutan.**
Las contraseñas no están dentro de las imágenes: van en el archivo `.env`, que nunca se sube.

```
 PC de desarrollo                 Docker Hub                  Servidor del instituto
 docker compose build   ──push──►  bramajo-web:1.0  ──pull──►  docker compose up -d
                                   bramajo-db:1.0
```

### 1. En casa: probar, construir y publicar

Una sola vez, crear la cuenta en hub.docker.com y entrar desde la terminal:

```bash
docker login
cp .env.ejemplo .env     # poner el usuario de Docker Hub en DOCKERHUB_USUARIO y cambiar las contraseñas
```

Cada vez que se termina una versión:

```bash
docker compose build             # arma las dos imágenes
docker compose up -d             # probarlas en la propia PC: https://localhost
docker compose push              # subirlas a Docker Hub
```

La primera vez MySQL tarda uno o dos minutos en cargar los datos. El certificado es autofirmado:
el navegador avisa y hay que aceptar.

### 2. En el servidor (AlmaLinux 8.10 sobre Proxmox): instalar Docker, una sola vez

```bash
sudo dnf -y install dnf-plugins-core
sudo dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
sudo dnf -y install docker-ce docker-ce-cli containerd.io docker-compose-plugin --allowerasing
sudo systemctl enable --now docker
sudo usermod -aG docker $USER      # para usar docker sin sudo; cerrar sesión y volver a entrar
docker --version && docker compose version
```

`--allowerasing` hace falta porque AlmaLinux trae Podman instalado y choca con Docker.
Si el puerto 80 está ocupado por el Apache del sistema: `sudo systemctl disable --now httpd`.

### 3. En el servidor: descargar y levantar

En el servidor solo hacen falta **dos archivos**: `docker-compose.yml` y `.env`.

```bash
sudo mkdir -p /opt/bramajo/sistema && sudo chown $USER /opt/bramajo/sistema
cd /opt/bramajo/sistema
# copiar acá docker-compose.yml y .env.ejemplo (con git clone, scp o pendrive)
cp .env.ejemplo .env && nano .env    # usuario de Docker Hub, contraseñas y la IP del servidor
chmod 600 .env
docker compose pull                  # descarga las imágenes de Docker Hub
docker compose up -d
docker compose ps                    # esperar a que bramajo_db diga "healthy"
```

Abrir el firewall para la web (el 3306 de MySQL queda cerrado):

```bash
sudo firewall-cmd --permanent --add-service=http --add-service=https
sudo firewall-cmd --reload
```

Comprobar: `curl -kI https://localhost` en el servidor, o `https://IP-del-servidor` desde otra PC.

### 4. Publicar una versión nueva

```bash
# En casa: cambiar VERSION=1.1 en .env y después
docker compose build && docker compose push
# En el servidor: cambiar VERSION=1.1 en .env y después
docker compose pull && docker compose up -d
```

Los datos no se pierden: la base vive en el volumen `datos`, no en la imagen.
Para volver a cero con los datos de prueba: `docker compose down -v && docker compose up -d` (**borra la base**).

### Comandos útiles

```bash
docker compose ps              # estado de los contenedores
docker compose logs -f app     # qué está pasando en la aplicación
docker compose logs db         # mensajes de MySQL (útil si la base no arranca)
docker compose down            # apagar (los datos se conservan)
```

### Scripts del servidor

La carpeta `servidor/` tiene los scripts de Administración de Sistemas Operativos. Se instalan una sola vez:

```bash
cd servidor && sudo ./instalar.sh      # copia a /opt/bramajo/scripts, guarda contraseñas y programa cron
sudo ./firewall.sh aplicar             # firewalld: 80 y 443 abiertos, 22 solo desde la red del instituto
sudo ./endurecer.sh                    # SSH con clave, fail2ban, política de contraseñas, actualizaciones
sudo ./antivirus.sh instalar           # ClamAV
```

Después todo se maneja desde el menú `sudo /opt/bramajo/scripts/servidor.sh`.

| Script | Qué hace | Cuándo |
|---|---|---|
| backup.sh | Respaldo verificado de la base y de las claves, 14 días de retención | cron, 02:00 |
| restaurar.sh | Restaura un respaldo con confirmación | a mano |
| mantenimiento.sh | Libera bloqueos vencidos e informa el estado | cron, cada hora |
| monitoreo.sh | Disco, memoria, contenedores, servicios, HTTPS y último respaldo | cron, cada 5 min |
| antivirus.sh | Escaneo con ClamAV y cuarentena | cron, 03:30 |
| consultas_bd.sh | Menú de consultas a la base con el usuario de solo lectura | a mano |
| firewall.sh, endurecer.sh | Seguridad del sistema operativo | una vez |
| gestion.sh y módulos | Usuarios, grupos, servicios y permisos del sistema | a mano |
| servidor.sh | Menú general que llama a todos los anteriores | a mano |

## Usuarios de la base de datos

| Usuario | Para qué | Permisos |
|---|---|---|
| root | Crear la base la primera vez | Todos (solo dentro del contenedor) |
| bramajo_app | La aplicación | SELECT, INSERT, UPDATE, DELETE. En `auditoria` solo SELECT e INSERT |
| bramajo_backup | Respaldos y monitoreo | Solo lectura |

## Pruebas

Pruebas unitarias de los tres formatos y del validador:

```bash
docker run --rm -v "$PWD":/app -w /app php:8.3-cli \
  sh -c "curl -sLO https://phar.phpunit.de/phpunit-11.phar && php phpunit-11.phar"
```
# bramajo-final
