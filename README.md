# AtomTerm

AtomTerm is a telnet server that deploys a directory as a navigable menu when
accessed via telnet clients, similar to a BBS.

## Usage

### Server

Given a root directory, AtomTerm parses the directory and creates a "web page"
with menus and documents, example directory under `menu/` folder of this repository
looks something like this:

```
menu/
├─ submenu/
│  ├─ empty.txt
│  ├─ laika.txt
│  ├─ nested_document.txt
│  ├─ meta.json
├─ meta.json
├─ document.txt

```

Each folder is a menu that contains links to menus and documents under it, meanwhile
each `*.txt` file is a document whose first line is its title.

`meta.json` files include information about a specific menu, namely, a `title` and
`description`.

Root directory, host and root are determined by a `config.json`, whose example can
be seen in this repository.

### Client

Any telnet client should be able to connect to a server run by AtomTerm, depending
on whether or not it has NAWS support, AtomTerm will also fill up the terminal
screen.

## Installation

### With Composer

You can require this package using composer

```bash
composer require ambertide/atomterm
```

When this is done, `serve.php` will be added to your 
`vendor/bin` directory (or wherever you put your vendor
binaries), and you can execute it:

```bash
php vendor/bin/serve.php
```

This will then the server.

### With Docker

You can also run this using the included docker file,
however, Docker version of this package will look for your 
files under the "/static" directory of the docker host, as 
such you need to include a mount under /static.

### Locally

Finally, you can also locally install the repository 
itself. Although Atomterm does not have external 
dependencies, it does require a PHP installation with 
`sockets` extension enabled and above version 8.3.

```bash
git clone https://github.com/ambertide/AtomTerm.git`
cd AtomTerm
composer install
composer dump-autoload
mv config.json.default config.json
composer run serve -- config.json
```
