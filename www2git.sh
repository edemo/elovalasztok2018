#!/bin/bash
# local github repo path
repo=e:/github-repok/elovalasztok
# local web server document_root path
www=e:/www/elovalasztok
# copy files from local web server into local github repo
cp -R -v $www/elovalasztok/* $repo/elovalasztok/*
cp -R -v $www/templates/elovalasztok/* $repo/templates/*