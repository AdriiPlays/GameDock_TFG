FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

# Dependencias necesarias
RUN dpkg --add-architecture i386 && \
    apt-get update && apt-get install -y \
    wget \
    ca-certificates \
    lib32gcc-s1 \
    lib32stdc++6 \
    libc6-i386 \
    libgcc1 \
    libstdc++6 \
    libglu1-mesa \
    libxcursor1 \
    libxrandr2 \
    libxss1 \
    libxtst6 \
    libnss3 \
    libxcomposite1 \
    libasound2 \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Crear usuario steam
RUN useradd -m steam
USER steam
WORKDIR /home/steam

# Instalar SteamCMD
RUN mkdir -p /home/steam/steamcmd && \
    cd /home/steam/steamcmd && \
    wget https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz && \
    tar -xvzf steamcmd_linux.tar.gz

# Instalar Unturned Dedicated Server (requiere login)
RUN /home/steam/steamcmd/steamcmd.sh \
    +force_install_dir /home/steam/unturned \
    +login Gamedocktfgadrian Adrian1212 \
    +app_update 1110390 validate \
    +quit

# Fix steamclient.so runtime path
RUN mkdir -p /home/steam/.steam/sdk64 && \
    ln -s /home/steam/steamcmd/linux64/steamclient.so /home/steam/.steam/sdk64/steamclient.so

WORKDIR /home/steam/unturned

EXPOSE 27015/udp
EXPOSE 27015/tcp

# Arranque automático del servidor
CMD ["./Unturned_Headless.x86_64", "-nographics", "-batchmode", "+secureserver", "DockerServer"]
