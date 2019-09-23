# Killposer

### Easy way to find and delete unused composer `/vendor/` directories.

![](/doc/usage.gif)

This is a cli-tool to find all composer vendor directories in a given path and remove all those you do not need any more.

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
$ composer killposer
```

### Command options

* `--path`, `-p` to specify the directory to search, default value is current directory, `./`
* `--byte-format`, `-f` available formats are: `kib`, `mib` (the default one), `gib`
* `--byte-threshold`, `-t` if for some reason you have no interest in the exact size of your vendors, you may set a threshold after which file size won't be calculated

### Example

Search vendors in `PhpProjects`

```bash
$ composer killposer -p '~/PhpProjects/'
```