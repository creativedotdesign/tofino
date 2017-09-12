#!/usr/bin/env bash

. ~/.nvm/nvm.sh # Include reference to NVM

echo 'Composer install deps...'
composer install

echo 'Install and use Node v6.7...'
nvm install 6.7.0
nvm use 6.7

echo 'Install Yarn from npm...'
npm install -g yarn

echo 'Yarn install deps...'
yarn -v
yarn install

echo 'Run first build...'
npm build