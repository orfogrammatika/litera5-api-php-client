#!/usr/bin/env bash

CHECK=$1

curl -X POST http://localhost:8899/results.php \
  --data-urlencode check="${CHECK}"
