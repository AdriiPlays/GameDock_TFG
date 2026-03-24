#!/bin/bash

echo "Iniciando contenedor Python..."

# Ejecutar main.py y enviar logs a main.log
if [ -f /home/python/app/main.py ]; then
    echo "Ejecutando main.py..."
    python3 /home/python/app/main.py 2>&1 | tee -a /home/python/app/main.log &
else
    echo "main.py no encontrado, esperando a que el usuario lo cree..."
fi

# Asegurar que el archivo existe
touch /home/python/app/main.log

# ENVIAR main.log AL STDOUT DEL CONTENEDOR
tail -f /home/python/app/main.log &
tail -f /dev/null
