# 🚀 Panel de Gestión de Contenedores Docker
Un panel moderno, rápido y modular para administrar contenedores Docker, servidores de juegos y archivos internos desde una interfaz web intuitiva.

---

## ✨ Características principales

### 🐳 Gestión de contenedores Docker
- Ver contenedores activos
- Acceso directo al sus archivos
- Reiniciar / detener / iniciar contenedores
- Logs en tiempo real
- Consola interactiva

### 📁 FTP integrado
Un gestor de archivos completo dentro del navegador:

- 📂 Navegación entre carpetas  
- 📄 Visualización de archivos  
- ⬆️ Subida de archivos  
- ⬇️ Descarga directa  
- 🗑️ Borrado de archivos y carpetas  
- ✏️ Editor de archivos integrado  
- 📁 Crear carpetas  
- Totalmente compatible con Windows y Linux  
- Funciona con **cualquier contenedor Docker**

### 🔧 Compatible con cualquier servidor
Funciona con:
- Minecraft (Paper, Spigot, Forge, Fabric…)
- Terraria
- Rust
- FiveM
- Servidores web (Nginx, Apache)
- Bots
- Bases de datos (solo lectura recomendada)
- Cualquier contenedor Docker que tengas

---

## 🧠 ¿Cómo funciona?

El panel se comunica con Docker mediante:

- `docker exec` → para ejecutar comandos dentro del contenedor  
- `docker cp` → para copiar archivos dentro y fuera del contenedor  

Esto permite:
- Navegar por el sistema de archivos del contenedor  
- Editar archivos de configuración  
- Subir y descargar contenido  
- Manipular carpetas y archivos sin exponer rutas del host  

Todo de forma segura y sin depender del sistema operativo.

---
---

## 🔄 Sistema de actualizaciones (en desarrollo)

El panel incluye un sistema preparado para:

- Comprobar automáticamente si hay una nueva versión en GitHub  
- Comparar con la versión instalada  
- Descargar y aplicar actualizaciones  
- Mantener el panel siempre al día  

Próximamente:
- Backups automáticos antes de actualizar  
- Rollback a versiones anteriores  

---

## 🛠️ Requisitos

- PHP 8+
- Docker instalado y accesible desde PHP
- Apache / Nginx
- Extensiones PHP: `json`, `curl`, `zip`

---

## 🚀 Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/AdriiPlays/GameDock_TFG.git

 ## 🚀 Roadmap
 
 [ ] Nuvos contenedores
 
 [x] FTP completo
 
 [x] Editor de archivos

 [x] Crear / borrar / subir / descargar

 [ ] Logs en tiempo real

 [x] Consola web (docker exec interactivo)

 [ ] Sistema de actualizaciones automático

[x] Sistema de permisos por usuario

##📜 Licencia
Este proyecto está bajo la licencia MIT.
Puedes usarlo, modificarlo y adaptarlo libremente.
