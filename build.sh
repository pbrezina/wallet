#!/bin/bash

unlink dist/wallet/backend
ng build
ln -s ../../backend dist/wallet/backend

