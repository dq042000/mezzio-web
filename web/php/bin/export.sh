#!/bin/sh
rm -f .data/temp/*
vendor/bin/mysql-workbench-schema-export --config=db/db-mwb.json db/mezzio.mwb