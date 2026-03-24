FROM python:3.12-slim

RUN useradd -m pythonuser

RUN mkdir -p /home/python/app
WORKDIR /home/python/app

COPY requirements.txt /home/python/app/requirements.txt
RUN pip install --upgrade pip
RUN pip install -r /home/python/app/requirements.txt || true

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R pythonuser:pythonuser /home/python
USER pythonuser

ENTRYPOINT ["/entrypoint.sh"]
