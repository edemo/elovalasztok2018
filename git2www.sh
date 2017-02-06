#!/bin/bash
# local github repo path
repo=e:/github-repok/elovalasztok
# local web server document_root path
www=e:/www/elovalasztok
# copy files from local repo into local web server
cp -R -v $repo/elovalasztok/* $www/elovalasztok/*
cp -R -v $repo/templates/* $www/templates/elovalasztok/*