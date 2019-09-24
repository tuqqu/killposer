# Killposer

### Easy way to find and delete composer-created `/vendor/` directories that you do not need any more.

![](/doc/usage.gif)

This is a cli-tool to find Composer-created vendor directories on your system, list their path and size, and it allows you delete ones that you don't need to free up storage space.

The project is inspired by [npkill](https://github.com/voidcosmos/npkill).

## Installation

Install Killposer globally with Composer: 

```bash
$ composer global require tuqqu/killposer
```

You have to make sure that global Composer binary directory is in your `PATH`. See [Composer docs](https://getcomposer.org/doc/03-cli.md#global).
On a Unix system run the following command:
```bash
$ export PATH="$PATH:$HOME/.composer/vendor/bin"
```

## Usage

Having installed it globally you may now use `killposer` binary:
```bash
$ killposer 
```

Use `W` and `S` keys to move up/down, `K` to delete the selected vendor and `Q` to quit.

### Command options

* `--path`, `-p` to specify the directory to search, default value is current directory, `./`
* `--byte-format`, `-f` available formats are: `kib`, `mib` (the default one), `gib`
* `--byte-threshold`, `-t` if for some reason you have no interest in the exact size of your vendors, you may set a threshold after which file size won't be calculated

### Example

Search vendors in `PhpProjects`

```bash
$ killposer -p '~/PhpProjects/'
```
