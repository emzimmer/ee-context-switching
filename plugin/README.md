# Quick Webpack SCSS/JS Compiler

This program provides a base to quickly get up and running with SCSS and JS compilation. It's light weight and designed for fast implementations, and it could be used for larger applications with some basic modifications. All you need to do is clone this repo and run yarn to install the required modules.

## Getting Set Up
1. Run command: git clone https://github.com/emzimmer/webpack-scss-js-compiler.git
2. If needed, move files into appropriate directory.
3. Run command: yarn

## How it Works
Webpack compiles JS and SCSS with a variety of modules. The entry point for the each file type/extension is at src > [extension] > index.[extension]. Outputs will be found at dist > main.min.[extension].

## Commands
### yarn build
Build production-ready JS and CSS files.

### yarn start
Build developer-friendly JS and watch for changes in (SCS|J)S files within the src directory. Update the output files any time a change is saved.

## Node version
Using the latest node version is ideal. Currently, that is 14.17.0. If your server or global environment runs another version and you don't have nvm running, here's how to get that set up:

1. Run: curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash
2. Verify it worked (output will be "nvm"): command -v nvm
3. Install latest: nvm install --lts

For all the nvm documentation: https://github.com/nvm-sh/nvm
