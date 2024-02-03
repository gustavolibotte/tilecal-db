FROM python:3.10.12

RUN pip install --upgrade pip
RUN pip install pandas matplotlib numpy jupyterlab openpyxl