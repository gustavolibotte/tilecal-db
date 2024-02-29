FROM python:3.10.12

RUN pip install --upgrade pip
COPY testing/requirements.txt .
RUN pip install -r requirements.txt