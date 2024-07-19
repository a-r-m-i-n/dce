# DCE-Extension for TYPO3

## What is DCE?

DCE is an extension for TYPO3 CMS, which creates easily and fast *dynamic content elements*.
Based on Extbase, Fluid and over 10 years of experience.


### Screenshots

![DCE General Configuration](Documentation/FirstSteps/Images/first-dce.png)

![Inline Templating in DCE](Documentation/FirstSteps/Images/template-default.png)


## Installation

You can install DCE in TYPO3 CMS using the [TER](https://extensions.typo3.org/extension/dce/)
or use composer to fetch DCE from [packagist](https://packagist.org/packages/t3/dce):

```
composer req t3/dce:"^3.1"
```


## Documentation

The full documentation can be found here: https://docs.typo3.org/p/t3/dce/master/en-us/


## How to contribute?

Just fork this repository and create a pull request to the **master** branch.
Please also describe why you've submitted your patch. If you have any questions feel free to contact me.

In case you can't provide code but want to support DCE anyway, here is my [PayPal donation link](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2DCCULSKFRZFU).

**Thanks to all contributors and sponsors!**


## DDEV Environment

DCE ships a [DDEV configuration](https://github.com/a-r-m-i-n/ddev-for-typo3-extensions), which allows you to test DCE in any TYPO3 version:

- 12.4
- 13.x

It uses Apache2 with php-fpm (8.2) enabled.

### Requirements

- Docker
- Docker Compose
- DDEV

### Setup

1. Start the DDEV containers using
    ```
    ddev start
    ```
2. Next execute the following commands
    ```
    ddev install-v12
    ddev install-v13
    ```
3. On https://dce.ddev.site/ you get a brief overview of the environment


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
