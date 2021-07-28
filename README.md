# DCE-Extension for TYPO3

## What is DCE?

DCE is an extension for TYPO3 CMS, which creates easily and fast *dynamic content elements*.
Based on Extbase, Fluid and over 8 years of experience.


### Screenshots

![DCE General Configuration](Documentation/FirstSteps/Images/first-dce.png "DCE General Configuration")

![Inline Templating in DCE](Documentation/FirstSteps/Images/template-default.png "Inline Templating in DCE")


## Installation

You can install DCE in TYPO3 CMS using the [TER](https://extensions.typo3.org/extension/dce/)
or use composer to fetch DCE from [packagist](https://packagist.org/packages/t3/dce):

```
composer req t3/dce:"^2.7"
```


## Documentation

The full documentation can be found here: https://docs.typo3.org/p/t3/dce/master/en-us/


## How to contribute?

Just fork this repository and create a pull request to the **master** branch.
Please also describe why you've submitted your patch. If you have any questions feel free to contact me.

In case you can't provide code but want to support DCE anyway, here is my [PayPal donation link](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2DCCULSKFRZFU).

**Thanks to all contributors and sponsors!**


## DDEV Environment

DCE ships a DDEV configuration, which allows you to test DCE in any TYPO3 version:

- 9.5
- 10.4
- 11.x

It uses Apache2 with php-fpm (7.4) enabled.

### Requirements

- Docker
- Docker Compose
- DDEV

### Setup

1. Start the DDEV containers using
    ```
    ddev start
    ```
2. Next execute one of the following commands
    ```
    ddev install-v9
    ddev install-v10
    ddev install-v11
    ddev install-all
    ```
   *Note: You can also skip the initial ``ddev start`` and enter one of the install commands first*
3. On https://dce.ddev.site/ you get a brief overview of the environments

When you ``ddev stop`` your containers, all files will be remain in Docker volume. To clean up use:
```
docker volume rm dce-v9-data
docker volume rm dce-v10-data
docker volume rm dce-v11-data
```

### Scripts

Besides the installation scripts, DCE also provides host commands in DDEV, to
render and preview the documentation.

**Render documentation:**
```
ddev docs
```

**Preview rendered documentation:**
```
ddev launch-docs
```
It only opens the browser with the right location. Please render the documentation first.
